<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MedstatNormUpload;
use App\Document;

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
/*        \Storage::put(
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
        echo "В загруженной базе данных ". $numrecordes . " строк (по 60 полей в каждой строке) <br>";

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
        }*/
        $no_zero_uploaded = MedstatNormUpload::count();
        $available_years = MedstatNormUpload::groupby(['id', 'year'])->distinct()->get(['year']);
        $available_units = MedstatNormUpload::groupby(['id', 'ucode'])->distinct()->get(['ucode']);
        $available_forms = MedstatNormUpload::groupby(['id', 'form'])->distinct()->with('medinfoform')->get(['form'])->sortBy('form');
        $periods = \App\Period::all();
        $units = \App\Unit::primary()->get();
        //dd($available_forms[0]->medinfoform->form_code);
        return view('jqxadmin.medstatimportintermediateresult', compact(
            'no_zero_uploaded',
            'available_years',
            'available_units',
            'available_forms',
            'periods',
            'units'));
    }

    public function makeMedstatImport(Request $request)
    {
        $this->validate($request, [
                'period' => 'required|integer',
                'unit' => 'required|integer',
            ]
        );
        $default_type = 1;

        $forms = MedstatNormUpload::groupby(['id', 'form'])->distinct()->with('medinfoform')->get(['form'])->sortBy('form');
        foreach ($forms as $form) {
            $document = Document::OfTUPF($default_type, $request->unit, $request->period );
        }
    }
}
