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
        //set_time_limit(180);
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
        $chunk = 1000;
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
            if ( fmod($i, $chunk) == 0  xor $i == $numrecords ) {
                //dump($i);
                $values = implode(', ', $v );
                $res = \DB::insert($insert . $values);
                $v = [];
            }
        }
        $i--;
        //$i = 1;
        $monitorings = \App\Monitoring::all();
        $albums = \App\Album::all()->sortBy('album_name');
        $periods = \App\Period::all();
        $states = \App\DicDocumentState::all();
        return view('jqxadmin.medstatNSimportIntermediateResult', compact( 'i',
            'monitorings',
            'albums',
            'periods',
            'states'
            ));
    }

    public function makeNSMedstatDataImport(Request $request)
    {
        $this->validate($request, [
                'monitoring' => 'required|integer',
                'album' => 'required|integer',
                'period' => 'required|integer',
                'state' => 'required|integer',
            ]
        );
        $m = $request->monitoring;
        $a = $request->album;
        $p = $request->period;
        $s = $request->state;
        // обрабатываем только первичные документы
        $t = 1;

        //$mo_form_select_query = "SELECT v.hospital, f.form_name, f.medstat_code, ff.id, ff.form_code FROM medstat_nsk_data v
        $mo_form_select_query = "SELECT v.hospital, ff.id FROM medstat_nsk_data v 
            left JOIN medstat_nsk_table_links t on t.id = v.table
            left join medstat_nsk_form_links f on f.id = t.form_id
            left join forms ff on ff.medstat_code = f.medstat_code
            left join mo_hierarchy u on u.id = v.hospital
            group by v.hospital, f.id, ff.id;";

        // mo, form
        $mo_form = \DB::select($mo_form_select_query);
        //dd($mo_form);
        // Сперва нужно создать/очистить отчетные документы
        $d = 0;
        $docs = [];
        foreach ($mo_form as $mf) {
            $document = \App\Document::firstOrNew([
                    'dtype' => $t,
                    'ou_id' => $mf->hospital,
                    'period_id' => $p,
                    'form_id' => $mf->id,
                ]);
                $document->monitoring_id = $m;
                $document->album_id = $a;
                $document->state = $s;
                $document->save();
                \App\Cell::OfDocument($document->id)->delete();
                $docs[] = $document;
                $d++;
        }

        $insert_transposed = "INSERT INTO statdata (doc_id, table_id, row_id, col_id, \"value\")
          (SELECT d.id doc_id, t.id table_id, r.id row_id, c.id column_id, v.data \"value\" FROM medstat_nsk_data v
            LEFT JOIN tables t ON t.medstatnsk_id = v.\"table\" AND t.transposed = 1
            LEFT JOIN mo_hierarchy u ON u.id = v.hospital
            LEFT JOIN rows r ON r.medstatnsk_id = v.row AND r.table_id = t.id
            LEFT JOIN columns c ON c.table_id = t.id AND c.column_index = 3
            LEFT JOIN forms f ON f.id = t.form_id
            LEFT JOIN documents d ON d.ou_id = u.id AND d.dtype = $t AND monitoring_id = $m AND d.album_id = $a AND d.period_id = $p AND d.form_id = f.id 
            WHERE d.id IS NOT NULL AND r.id IS NOT NULL);";
        $transposed_values = \DB::insert($insert_transposed);

        $insert_flat = "INSERT INTO statdata (doc_id, table_id, row_id, col_id, \"value\")
          (SELECT d.id doc_id, t.id table_id, r.id row_id, c.id column_id, v.data \"value\" FROM medstat_nsk_data v
            LEFT JOIN mo_hierarchy u ON u.id = v.hospital
            LEFT JOIN tables t ON t.medstatnsk_id = v.\"table\" AND t.transposed = 0
            LEFT JOIN rows r ON r.medstatnsk_id = v.row AND r.table_id = t.id
            LEFT JOIN columns c ON c.medstatnsk_id = v.\"column\" AND c.table_id = t.id
            LEFT JOIN forms f ON f.id = t.form_id
            LEFT JOIN documents d ON d.ou_id = u.id AND d.dtype = $t AND monitoring_id = $m AND d.album_id = $a AND d.period_id = 6 AND d.form_id = f.id
            WHERE d.id IS NOT NULL AND r.id IS NOT NULL AND c.id IS NOT NULL);";
        $flat_values = \DB::insert($insert_flat);

        return view('jqxadmin.medstatNSimportDataresult', compact( 'd', 'transposed_values'

        ));
    }
}
