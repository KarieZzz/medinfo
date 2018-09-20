<?php

namespace App\Http\Controllers\ImportExport;

use App\MedstatNskTableLink;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MedstatNskControl;
use App\Form;
use App\Table;
use App\CFunction;

class MedstatNskControlImportController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function selectFileNSMedstatControls()
    {
        return view('jqxadmin.medstatNSimportControls');
    }

    public function uploadFileNSMedstatControlCsv(Request $request)
    {
        $this->validate($request, [
                'medstat_nsk_controls' => 'required|file',
            ]
        );
        if (!$request->skip_upload) {
            set_time_limit(600);
            \Storage::put(
                'medstat_uploads/medstat_nsk_controls.zip',
                file_get_contents($request->file('medstat_nsk_controls')->getRealPath())
            );
            $zip_file = storage_path('app/medstat_uploads/medstat_nsk_controls.zip');
            $zip = new \ZipArchive();
            if ($zip->open($zip_file) === TRUE) {
                $data =  $zip->getFromName('Int_control.csv');
                $zip->close();
            } else {
                throw  new \Exception("Не удалось открыть файл архива $zip_file");
            }
            \Storage::put('medstat_uploads/nskcontrols.csv', $data);
            $file = storage_path('app/medstat_uploads/nskcontrols.csv');
            \App\MedstatNskControl::truncate();
            $insert = "COPY medstat_nsk_controls (id, form, \"table\", error_type, \"left\", \"right\", \"relation\", cycle, comment) FROM '$file' CSV;";
            $res = \DB::insert($insert);
            if (!$res) {
                abort(500, "Данные из файла $file не загружены");
            }
        }
/*        $find_duplicates = "SELECT f.form_code fcode, n.\"table\", n.\"left\", n.\"right\", n.relation, count(*) repeats FROM medstat_nsk_controls n
              LEFT JOIN forms f on f.medstatnsk_id = n.form
              WHERE f.id IS NOT NULL group by f.form_code, n.form, n.\"table\", n.\"left\", n.\"right\", n.relation having count(*) > 1
              ORDER BY f.form_code, n.\"table\", n.\"left\";";
        $find_duplicates = "SELECT f.form_code fcode, n.\"table\", n.\"left\", n.\"right\", n.relation, n.cycle, count(*) repeats FROM medstat_nsk_controls n
              LEFT JOIN forms f on f.medstatnsk_id = n.form 
              group by f.form_code, n.form, n.\"table\", n.\"left\", n.\"right\", n.relation, n.cycle having count(*) > 1 
              ORDER BY f.form_code, n.\"table\", n.\"left\";";
        $duplicates = \DB::select($find_duplicates);
        $dubs_count = count($duplicates);*/

        $delete_duplicates = 'DELETE FROM medstat_nsk_controls n WHERE n.id NOT IN (SELECT MIN(nn.id) FROM medstat_nsk_controls nn 
            GROUP BY nn.form, nn."table", nn."left", nn."right", nn.relation, nn.cycle);';
        $dubs_count = \DB::delete($delete_duplicates);
        $rec_count = MedstatNskControl::count();
        if ($rec_count === 0) {
            abort(500, "Кэш контролей Медстат (НСК) пуст, поворите загрузку контролей из файла");
        }
        $inter_form_count = MedstatNskControl::InterForm()->count();
        $inter_table_count = MedstatNskControl::InterTable()->count();
        $intable_count = MedstatNskControl::InTable()->count();
        $forms = \App\Form::orderBy('form_code')->get(['id', 'form_code', 'form_name']);
        return view('jqxadmin.medstatNSControlsimportIntermediateResult', compact( 'rec_count', 'inter_form_count',
            'inter_table_count', 'intable_count', 'dubs_count', 'forms' ));
    }

    public function makeNSMedstatControlImport(Request $request)
    {
        $this->validate($request, [
                'control_type_import' => 'required|array',
                'formids' => 'required',
                'initial_status' => 'required|in:1,2',
                'error_level' => 'required|in:1,2',
                'selectedallforms' => 'in:1,0',
            ]
        );
        $formids = explode(',', $request->formids);
        if ($request->initial_status === '1') {
            $blocked = false;
        } else {
            $blocked = true;
        }
        $forms = Form::whereIn('id', $formids)->get();
        if ($request->clear_old_controls) {
            $deleted_old = 0;
            foreach ($forms as $form) {
                $tables = Table::OfForm($form->id)->get();
                foreach ($tables as $table) {
                    $deleted_old += CFunction::OfTable($table->id)->InForm()->delete();
                }
            }
        }
        $function_id = 1; // Функция "сравнение"
        $converter = new \App\Medinfo\Control\ConvertNskControls($formids);
        $intables_saved_count = 0;
        $intertables_saved_count = 0;
        $interform_saved_count = 0;
        if (in_array('1', $request->control_type_import)) {
            $intables = $converter->covertInTableControls();
            //dd($intables);
            $control_type = 1; // Внутриформенный контроль
            foreach ( $intables['forms'] as  $intable_form ) {
                foreach ($intable_form['tables'] as $int_table) {
                    foreach ($int_table['scripts'] as $int_script) {
                        $newfunction = new CFunction();
                        $newfunction->table_id = $int_table['table']['table_id'];
                        $newfunction->level = $request->error_level;
                        $newfunction->script = $int_script['converted_script'];
                        $newfunction->comment = $int_script['comment'];
                        $newfunction->blocked = $blocked;
                        $newfunction->type = $control_type;
                        $newfunction->function = $function_id ;
                        //$newfunction->ptree = $cache['ptree'];
                        //$newfunction->properties = json_encode($cache['properties']);
                        $newfunction->save();
                        $intables_saved_count++;
                    }
                }
            }
        }
        if (in_array('2', $request->control_type_import)) {
            $intertables = $converter->convertInterTableControls();
            //dd($intertables);
            $control_type = 1; // Внутриформенный контроль
            foreach ( $intertables['forms'] as  $intertable_form ) {
                foreach ($intertable_form['tables'] as $inter_table) {
                    $newfunction = new CFunction();
                    $newfunction->table_id = $inter_table['table_id'];
                    $newfunction->level = $request->error_level;
                    $newfunction->script = $inter_table['scripts']['converted_script'];
                    $newfunction->comment = $inter_table['scripts']['comment'];
                    $newfunction->blocked = $blocked;
                    $newfunction->type = $control_type;
                    $newfunction->function = $function_id ;
                    $newfunction->save();
                    $intertables_saved_count++;
                }
            }
        }
        if (in_array('3', $request->control_type_import)) {
            $interforms = $converter->convertInterFormControls();
            //dd($interforms);
            if ($request->clear_old_controls) {
                $deleted_old_interform = CFunction::InterForm()->delete();
            }
            $control_type = 2; // Межформенный контроль
            foreach ($interforms['scripts'] as $interform) {
                $newfunction = new CFunction();
                $newfunction->table_id = $interform['table']['table_id'];
                $newfunction->level = $request->error_level;
                $newfunction->script = $interform['converted_script'];
                $newfunction->comment = $interform['comment'];
                $newfunction->blocked = $blocked;
                $newfunction->type = $control_type;
                $newfunction->function = $function_id;
                $newfunction->save();
                $interform_saved_count++;
            }
        }

        $all = $intables_saved_count + $intertables_saved_count + $interform_saved_count;
        return view('jqxadmin.medstatNSControlsimportFinalResult', compact( 'forms','all', 'intables_saved_count', 'intertables_saved_count', 'interform_saved_count'));
    }
}
