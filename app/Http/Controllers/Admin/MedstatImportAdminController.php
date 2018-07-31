<?php

namespace App\Http\Controllers\Admin;

use App\Form;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MedstatNormUpload;
use App\Document;
use App\Cell;

class MedstatImportAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        return view('jqxadmin.medstatimport');
    }

    public function uploadMedstatData(Request $request)
    {
        \Storage::put(
            'medstat_uploads/medctat.dbf',
            file_get_contents($request->file('medstat')->getRealPath())
        );
        $dbf_file = storage_path('app/medstat_uploads/medctat.dbf');
        $db = dbase_open($dbf_file, 0);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных medstat.dbf");
        }
        $numrecordes = dbase_numrecords($db);
        echo "В загруженной базе данных ". $numrecordes . " строк <br>";

        for ($i = 1; $i <= $numrecordes; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            //dd($ar);
            $upl = \App\MedstatUpload::create($ar);
            //dd($upl);
        }
        echo "В загружено в систему ". $i . " записей <br>";

        $ucodes = \App\MedstatUpload::groupby(['id', 'A2'])->distinct()->get(['A2']);
        echo "В выгрузке приведно три учреждения/территории: <br>";
        foreach ($ucodes as $ucode) {
            echo $ucode->A2 . '<br>';
        }

        dd(\App\MedstatUpload::where('A4', '0070000')->sum('A81'));
    }

    public function uploadNormalizedMedstatData(Request $request)
    {
        \Storage::put(
            'medstat_uploads/medctat.dbf',
            file_get_contents($request->file('medstat')->getRealPath())
        );
        $dbf_file = storage_path('app/medstat_uploads/medctat.dbf');
        $db = dbase_open($dbf_file, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных medstat.dbf");
        }
        dbase_pack($db);
        $numrecordes = dbase_numrecords($db);
        //echo "В загруженной базе данных ". $numrecordes . " строк (по 60 полей в каждой строке) <br>";

        for ($i = 1; $i <= $numrecordes; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $year = $ar['A1'];
            $ucode = $ar['A2'];
            $form = substr($ar['A4'], 0,5);
            $table = substr($ar['A5'], -4);
            $row = $ar['A6'];
            $graphs = array_values(array_slice($ar, 5, 50));
            foreach ($graphs as $key => $value) {
                if ($value !== 0.0) {
                    $key++;
                    $column = str_pad((string)($key), 2 , 0 , STR_PAD_LEFT);
                    $upl = MedstatNormUpload::create([
                        'year' => $year,
                        'ucode' => $ucode,
                        'form' => $form,
                        'table' => $table,
                        'row' => $row,
                        'column' => $column,
                        'value' => $value,
                    ]);
                    //dd($upl);
                }
            }
        }
        $no_zero_uploaded = MedstatNormUpload::count();
        $available_years = MedstatNormUpload::groupby(['id', 'year'])->distinct()->get(['year']);
        $available_units = MedstatNormUpload::groupby(['id', 'ucode'])->distinct()->get(['ucode']);
        $available_forms = MedstatNormUpload::groupby(['id', 'form'])->distinct()->with('medinfoform')->get(['form'])->sortBy('form');
        $monitorings = \App\Monitoring::all();
        $albums = \App\Album::all()->sortBy('album_name');
        $periods = \App\Period::all();
        $units = \App\Unit::primary()->get();
        $states = \App\DicDocumentState::all();
        //dd($available_forms[0]->medinfoform->form_code);
        return view('jqxadmin.medstatimportintermediateresult', compact(
            'no_zero_uploaded',
            'available_years',
            'available_units',
            'available_forms',
            'monitorings',
            'albums',
            'periods',
            'states',
            'units'));
    }

    public function makeMedstatImport(Request $request)
    {
        $this->validate($request, [
                'monitoring' => 'required|integer',
                'album' => 'required|integer',
                'period' => 'required|integer',
                'unit' => 'required|integer',
                'state' => 'required|integer',
            ]
        );
        set_time_limit(180);
        //dd(Form::where('id', 37)->first(['medstat_code'])->medstat_code);
        $default_type = 1;
        //$default_monitoring = 100001;
        //$default_album = 1;
        //$default_state = 4;
        $uploaded_forms = MedstatNormUpload::groupby(['id', 'form'])->distinct()->pluck('form');
        $forms = Form::all();
        foreach ($forms as $form) {
            if ($uploaded_forms->contains($form->medstat_code)) {
                $document = Document::firstOrNew([
                    'dtype' => $default_type,
                    'ou_id' => $request->unit,
                    'period_id' => $request->period,
                    'form_id' => $form->id,
                ]);
                $document->monitoring_id = $request->monitoring;
                $document->album_id = $request->album;
                $document->state = $request->state;
                $document->save();
                Cell::OfDocument($document->id)->delete();
                $affected = 0;
                $tables = \App\Table::OfForm($form->id)->OfMedstat()->get();
                foreach ($tables as $table) {
                    $rows = \App\Row::OfTable($table->id)->InMedstat()->get();
                    //$columns = \App\Column::OfTable($table->id)->InMedstat()->get();
                    $columns = \App\Column::OfTable($table->id)->get();

                        foreach ($rows as $row) {
                            foreach ($columns as $column) {
                                if (!$table->transposed) {
                                    $uploaded = MedstatNormUpload::OfFTRC($form->medstat_code, $table->medstat_code, $row->medstat_code, $column->medstat_code)->first();
                                } elseif ($table->transposed = 1) {
                                    $uploaded = MedstatNormUpload::OfFTRC($form->medstat_code, $table->medstat_code, '001', substr($row->medstat_code, -2))->first();
                                }
                                if (!is_null($uploaded)) {
                                    $cell = Cell::firstOrCreate(['doc_id' => $document->id, 'table_id' => $table->id, 'row_id' => $row->id, 'col_id' => $column->id]);
                                    $cell->value = $uploaded->value;
                                    $cell->save();
                                    $affected++;
                                }
                            }
                        }


                }

            }
        }
        return ['affected' => $affected];
    }
    // Импорт усреждений из Медстата (Новосибирск)
    public function selectFileNSMedstatUnits(Request $request)
    {
        return view('jqxadmin.medstatNSimportMO');
    }

    public function uploadFileNSMedstatUnits(Request $request)
    {
        \Storage::put(
            'medstat_uploads/medstat_ns_units.dbf',
            file_get_contents($request->file('medstat_ns_units')->getRealPath())
        );
        $dbf_file = storage_path('app/medstat_uploads/medstat_ns_units.dbf');
        $db = dbase_open($dbf_file, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных medstat_ns_units.dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        //echo "В загруженной базе данных ". $numrecordes . " строк. <br>";

        \App\Unit::where('id', '<>', 0 )->delete();

        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $id = $ar['ID'];
            //$unit_code = (int)$ar['IND'];
            $unit_name = iconv('cp866', 'utf-8', $ar['UNIT']);
            $parent = (int)$ar['DEPENDENCE'];
            $country = (bool)$ar['COUNTRY'] ? 'true' : 'false';
            /*            $upl = \App\Unit::create([
                            'id' => $id,
                            'unit_code' => $id,
                            'unit_name' => $unit_name,
                            'parent_id' => $parent,
                            'countryside' => $country,
                        ]);*/
            $insert = "INSERT INTO public.mo_hierarchy ( id, parent_id, unit_code, unit_name, report , countryside ) 
              VALUES ( $id, $parent, '$id', '$unit_name', 1, $country )";
            $res = \DB::insert($insert);
            //dd($upl);
        }
        // Убираем лишний корневой элемент
        $first_el = \App\Unit::find(1)->delete();
        $first_childs = \App\Unit::where('parent_id', 1)->update(['parent_id' => 0]);
        $seq = "ALTER SEQUENCE unit_id_seq RESTART WITH $i;";
        \DB::update($seq);
        return view('jqxadmin.medstatNSimportMOresult', compact( 'numrecords'));
    }
}
