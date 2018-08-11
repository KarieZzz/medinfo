<?php

namespace App\Http\Controllers\ImportExport;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MedstatNskDataImportController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function selectFileNSMedstatData(Request $request)
    {
        return view('jqxadmin.medstatNSimportData');
    }

    public function uploadFileNSMedstatData(Request $request)
    {
        set_time_limit(360);
        \Storage::put(
            'medstat_uploads/medstat_nsk_data.zip',
            file_get_contents($request->file('medstat_nsk_data')->getRealPath())
        );
        $zip_file = storage_path('app/medstat_uploads/medstat_nsk_data.zip');
        $zip = new \ZipArchive();
        if ($zip->open($zip_file) === TRUE) {
            $data =  $zip->getFromName('Data.DBF');
            $zip->close();
        } else {
            throw  new \Exception("Не удалось открыть файл архива $zip_file");
        }
        \Storage::put('medstat_uploads/data.dbf', $data);

        $dbf_file = storage_path('app/medstat_uploads/data.dbf');
        if (!$db = dbase_open($dbf_file, 2)) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf_file");
        }
        //dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        \App\MedstatNskData::truncate();
        $insert = 'INSERT INTO public.medstat_nsk_data ( hospital, data, year, "table", "column" , "row" ) VALUES ';
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $unit = $ar['HOSPITAL'];
            $value = $ar['DATA'];
            $period = $ar['YEAR'];
            $table = $ar['TABLE'];
            $column = $ar['COLUMN'];
            $row = $ar['ROW'];
            $v[] = "( $unit, $value, $period, $table, $column, $row ) ";
            //dd($upl);
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        $monitorings = \App\Monitoring::all();
        $albums = \App\Album::all()->sortBy('album_name');
        $periods = \App\Period::all();
        $states = \App\DicDocumentState::all();
        return view('jqxadmin.medstatNSimportData', compact( 'numrecords',
            'monitorings',
            'albums',
            'periods',
            'states'
            ));
    }
}
