<?php

namespace App\Http\Controllers\ImportExport;

use App\MedstatNskTableLink;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MedstatNskControl;

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
              ORDER BY f.form_code, n.\"table\", n.\"left\";";*/
/*        $find_duplicates = "SELECT f.form_code fcode, n.\"table\", n.\"left\", n.\"right\", n.relation, n.cycle, count(*) repeats FROM medstat_nsk_controls n
              LEFT JOIN forms f on f.medstatnsk_id = n.form 
              group by f.form_code, n.form, n.\"table\", n.\"left\", n.\"right\", n.relation, n.cycle having count(*) > 1 
              ORDER BY f.form_code, n.\"table\", n.\"left\";";
        $duplicates = \DB::select($find_duplicates);*/
        //$dubs_count = count($duplicates);

        $delete_duplicates = "DELETE FROM medstat_nsk_controls n 
            WHERE n.id NOT IN (
            SELECT MIN(nn.id)
            FROM medstat_nsk_controls nn
            GROUP BY nn.form, nn.\"table\", nn.\"left\", nn.\"right\", nn.relation, nn.cycle );";
        $dubs_count = \DB::delete($delete_duplicates);

        $converter = new \App\Medinfo\Control\ConvertNskControls();
        //$converter->covertInTable();
        $converter->convertInterTable();

        $rec_count = MedstatNskControl::count();
        $inter_form_count = MedstatNskControl::InterForm()->count();
        $inter_table_count = MedstatNskControl::InterTable()->count();
        $intable_count = MedstatNskControl::InTable()->count();
        $forms = \App\Form::orderBy('form_code')->get(['id', 'form_code', 'form_name']);
        return view('jqxadmin.medstatNSControlsimportIntermediateResult', compact( 'rec_count', 'inter_form_count',
            'inter_table_count', 'intable_count', 'dubs_count', 'forms' ));
    }
}
