<?php

namespace App\Http\Controllers\ImportExport;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ImportMsRowsColumns extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admins');
    }

    public function index()
    {
        return view('jqxadmin.ms_rows_columns_import');
    }

    public function uploadMedstatSrtuct(Request $request)
    {
        //$mfcolumns = Column::OfTable(980)->OfDataType()->orderBy('column_index')->get();
        //dd($mfcolumns->count());
        $this->validate($request, [
                'medstat_struct' => 'required|file',
            ]
        );
        \Storage::put(
            'medstat_uploads/medstat_struct.zip',
            file_get_contents($request->file('medstat_struct')->getRealPath())
        );
        $zip_file = storage_path('app/medstat_uploads/medstat_struct.zip');
        $zip = new \ZipArchive();
        if ($zip->open($zip_file) === TRUE) {
            $grf =  $zip->getFromName('grf.dbf');
            $str = $zip->getFromName('str.dbf');
            $zip->close();
        } else {
            throw  new \Exception("Не удалось открыть файл архива $zip_file");
        }
        \Storage::put('medstat_uploads/grf.dbf', $grf);
        \Storage::put('medstat_uploads/str.dbf', $str);
        $grf_file = storage_path('app/medstat_uploads/grf.dbf');
        $str_file = storage_path('app/medstat_uploads/str.dbf');

        $str_count = $this->importStr($str_file);
        $grf_count = $this->importGrf($grf_file);

        return view('jqxadmin.medstatStructImportResult', compact( 'str_count', 'grf_count'));
    }

    public function importStr($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        //dd($numrecords);
        \App\MsStr::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            //$id = $ar['recid'];
            $a1 = iconv('cp866', 'utf-8', trim($ar['A1']));
            $a2 = pg_escape_string(iconv('cp866', 'utf-8', trim($ar['A2'])));
            $gt = $ar['GT'];
            $now = Carbon::now();
            $insert = "INSERT INTO public.ms_str ( rec_id, a1, a2, gt, syncronized_at ) VALUES ";
            $v[] = " ( $i, '$a1', '$a2', '$gt', '$now' ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        return $numrecords;
    }

    public function importGrf($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        //dd($numrecords);
        \App\MsGrf::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            //$id = $ar['recid'];
            $a1 = iconv('cp866', 'utf-8', trim($ar['A1']));
            $a2 = pg_escape_string(iconv('cp866', 'utf-8', trim($ar['A2'])));
            $gt = $ar['GT'];
            $a3 = $ar['A3'];
            $now = Carbon::now();
            $insert = "INSERT INTO public.ms_grf ( rec_id, a1, a2, gt, a3, syncronized_at ) VALUES ";
            $v[] = " ( $i, '$a1', '$a2', '$gt', '$a3', '$now' ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        return $numrecords;
    }

}
