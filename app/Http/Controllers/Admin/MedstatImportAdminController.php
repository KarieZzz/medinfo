<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MedstatNormUpload;
use App\Document;
use App\Form;
use App\Table;
use App\Row;
use App\Column;
use App\Cell;
use PhpOffice\PhpWord\Style\Tab;

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
        set_time_limit(360);
        $this->validate($request, [
                'monitoring' => 'required|integer',
                'album' => 'required|integer',
                'period' => 'required|integer',
                'unit' => 'required|integer',
                'state' => 'required|integer',
            ]
        );
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

    public function selectFileNSMedstatLinks(Request $request)
    {
        $albums = \App\Album::all()->sortBy('album_name');
        return view('jqxadmin.medstatNSimportLinks', compact('albums'));
    }

    public function uploadFileNSMedstatLinks(Request $request)
    {
        $this->validate($request, [
                'medstat_ns_links' => 'required|file',
                'album' => 'required|integer',
            ]
        );
        $album = $request->album;
        \Storage::put(
            'medstat_uploads/medstat_ns_links.zip',
            file_get_contents($request->file('medstat_ns_links')->getRealPath())
        );
        $zip_file = storage_path('app/medstat_uploads/medstat_ns_links.zip');
        $zip = new \ZipArchive();
        if ($zip->open($zip_file) === TRUE) {
            $forms =  $zip->getFromName('Forms.DBF');
            $tables = $zip->getFromName('Tables.DBF');
            $rows = $zip->getFromName('Rows.DBF');
            $columns = $zip->getFromName('Columns.DBF');
            $fl = $zip->getFromName('FL.DBF');
            $tl = $zip->getFromName('TL.DBF');
            $rl = $zip->getFromName('RL.DBF');
            $cl = $zip->getFromName('CL.DBF');
            $zip->close();
        } else {
            throw  new \Exception("Не удалось открыть файл архива $zip_file");
        }
        \Storage::put('medstat_uploads/forms.dbf', $forms);
        \Storage::put('medstat_uploads/tables.dbf', $tables);
        \Storage::put('medstat_uploads/rows.dbf', $rows);
        \Storage::put('medstat_uploads/columns.dbf', $columns);
        \Storage::put('medstat_uploads/fl.dbf', $fl);
        \Storage::put('medstat_uploads/tl.dbf', $tl);
        \Storage::put('medstat_uploads/rl.dbf', $rl);
        \Storage::put('medstat_uploads/cl.dbf', $cl);
        $forms_file = storage_path('app/medstat_uploads/forms.dbf');
        $tables_file = storage_path('app/medstat_uploads/tables.dbf');
        $rows_file = storage_path('app/medstat_uploads/rows.dbf');
        $columns_file = storage_path('app/medstat_uploads/columns.dbf');
        $fl_file = storage_path('app/medstat_uploads/fl.dbf');
        $tl_file = storage_path('app/medstat_uploads/tl.dbf');
        $rl_file = storage_path('app/medstat_uploads/rl.dbf');
        $cl_file = storage_path('app/medstat_uploads/cl.dbf');

        $form_count = $this->importNSForms($forms_file);
        $matched_forms = $this->matchingFormMSCode($fl_file);

        $tables = $this->importNSTables($tables_file);
        $table_count = $tables[0];
        $matched_tables = $tables[1];

        $rows = $this->importNSRows($rows_file);
        $row_count = $rows[0];
        $matched_rows = $rows[1];

        $columns = $this->importNSColumns($columns_file);
        $column_count = $columns[0];
        $matched_columns = $columns{1};
        //$matched_tables = $this->matchingTableMSCode($tl_file);
        //$matched_rows = $this->matchingRowMSCode($rl_file);
        $tansposed_nsktables = $this->matchingColumnMSCode($cl_file);
        $form_disparity = Form::whereNull('medstatnsk_id')->get();
        $table_disparity = Table::whereNull('medstatnsk_id')->with('form')->orderBy('form_id')->get();

        $transposed_disparity = $this->findTransposedTablesDisparity();

        return view('jqxadmin.medstatNSimportLinksresult',
            compact(
            'form_count',
                'table_count',
                'tansposed_nsktables',
                'row_count',
                'column_count',
                'matched_forms',
                'matched_tables',
                'matched_rows',
                'matched_columns',
                'form_disparity',
                'table_disparity',
                'transposed_disparity'
                ));
    }

    public function importNSForms($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        \App\MedstatNskFormLink::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $id = $ar['ID'];
            $form_name = iconv('cp866', 'utf-8', trim($ar['FORMNAME']));
            $decipher = iconv('cp866', 'utf-8', trim($ar['DECIPHER']));
            $ind = $ar['IND'];
            $insert = "INSERT INTO public.medstat_nsk_form_links ( id, form_name, decipher, ind ) VALUES ";
            $v[] = " ( $id, '$form_name', '$decipher', $ind ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        return $numrecords;
    }

    public function importNSTables($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        \App\MedstatNskTableLink::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $id = $ar['IDENT'];
            $form = $ar['FORM'];
            $tablen = iconv('cp866', 'utf-8', trim($ar['TABLEN']));
            $name = iconv('cp866', 'utf-8', trim($ar['NAME']));
            $colcount = $ar['COLCOUNT'];
            $rowcount = $ar['ROWCOUNT'];
            $fixcols = $ar['FIXCOLS'];
            $fixrows = $ar['FIXROWS'];
            $floattype = (bool)$ar['FLOATTYPE'] ? 'true' : 'false';
            $scan = $ar['SCAN'];
            $insert = "INSERT INTO public.medstat_nsk_table_links ( id, form_id, tablen, name, colcount, rowcount, fixcol, fixrows, floattype, scan ) VALUES ";
            $v[] = " ( $id, $form, '$tablen' , '$name', $colcount, $rowcount, $fixcols, $fixrows, $floattype, $scan ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        $forms = Form::whereNotNull('medstatnsk_id')->get();
        $cleaned_table_ids = Table::whereNotNull('medstatnsk_id')->update(['medstatnsk_id' => null ]);
        $t = 0;
        foreach ($forms as $form) {
            $linked_tables = \App\MedstatNskTableLink::where('form_id', $form->medstatnsk_id)->get();
            foreach ($linked_tables as $linked_table) {
                $fullcode = $linked_table->tablen;
                $trimedcode = preg_match('/\((?:[0-9.]*)(\d{4})\)/u', $fullcode, $match);
                $mftable = Table::OfFormTableCode($form->id, $match[1])->first();
                if ($mftable) {
                    $mftable->medstatnsk_id = $linked_table->id;
                    $mftable->save();
                    $t++;
                }
            }
        }
        return [ $numrecords , $t, $cleaned_table_ids ];
    }

    public function importNSRows($dbf)
    {
/*        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        //echo "В загруженной базе данных ". $numrecordes . " строк. <br>";

        \App\MedstatNskRowLink::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $t = $ar['TABLE'];
            $row = $ar['ROW'];
            $insert = 'INSERT INTO public.medstat_nsk_row_links ( "table", "row" ) VALUES ';
            $v[] = " ( $t , $row ) ";

            //dd($upl);
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);*/

        $tables = Table::whereNotNull('medstatnsk_id')->get();
        $cleaned_row_ids = Row::whereNotNull('medstatnsk_id')->update(['medstatnsk_id' => null ]);
        $all_rows = 0;
        $matched_rows = 0;
        foreach ($tables as $table) {
            $nsktable = \App\MedstatNskTableLink::where('id', $table->medstatnsk_id)->first();
            $offset = $nsktable->fixrows + 1;
            $nskrow_count = $nsktable->rowcount - $nsktable->fixrows;
            //for ($i = 1; $i <= $nsktable->rowcount; $i++) {
            for ($i = 1; $i <= $nskrow_count; $i++) {
                $all_rows++;
                $mfrow = Row::OfTableRowIndex($table->id, $i)->first();
                if ($mfrow) {
                    $mfrow->medstatnsk_id = $i + $offset;
                    $mfrow->save();
                    $matched_rows++;
                }
            }
        }

        return [ $all_rows, $matched_rows, $cleaned_row_ids ];
    }

    public function importNSColumns($dbf)
    {
/*        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        //echo "В загруженной базе данных ". $numrecordes . " строк. <br>";

        \App\MedstatNskColumnLink::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $table = $ar['TABLE'];
            $column = $ar['COLUMN'];
            $insert = 'INSERT INTO public.medstat_nsk_column_links ( "table", "column" ) VALUES ';
            $v[] = " ( $table , $column ) ";

            //dd($upl);
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);*/
        // в транспонированных таблицах коды Медстат НСК не прописываем, нет необходимости
        $tables = Table::whereNotNull('medstatnsk_id')->where('transposed', 0)->get();
        $cleaned_column_ids = Column::whereNotNull('medstatnsk_id')->update(['medstatnsk_id' => null ]);
        $all_columns = 0;
        $matched_columns = 0;
        foreach ($tables as $table) {
            $nsktable = \App\MedstatNskTableLink::where('id', $table->medstatnsk_id)->first();
            $offset = $nsktable->fixcol + 1;
            //$nskcol_count = $nsktable->colcount - $nsktable->fixcol;
            for ($i = $offset; $i <= $nsktable->colcount; $i++) {
                $all_columns++;
                $mfcolumn = Column::OfTableColumnIndex($table->id, $i)->first();
                if ($mfcolumn) {
                    $mfcolumn->medstatnsk_id = $i + 1;
                    $mfcolumn->save();
                    $matched_columns++;
                }
            }
        }

        return [ $all_columns, $matched_columns, $cleaned_column_ids ];
    }

    public function matchingFormMSCode($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $form_code =  iconv('cp866', 'utf-8', trim($ar['NF']));
            $medstat_code = trim($ar['MF']);
            $matched = \App\MedstatNskFormLink::OfCode($form_code)->first();
            if ($matched) {
                $matched->medstat_code = $medstat_code;
                $matched->save();
            }
        }
        $formlinks = \App\MedstatNskFormLink::whereNotNull('medstat_code')->get();
        $cleaned_forms_ids = Form::whereNotNull('medstatnsk_id')->update(['medstatnsk_id' => null ]);
        //dd($formlinks);
        $matched_forms = 0;
        foreach ($formlinks as $formlink) {
            $form = Form::OfMedstatCode($formlink->medstat_code)->first();
            if ($form) {
                $form->medstatnsk_id = $formlink->id;
                $form->save();
                $matched_forms++;
            }
        }
        return $matched_forms;
    }

/*    public function matchingTableMSCode($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        \App\MedstatNskMskTableMatching::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $mds = iconv('cp866', 'utf-8', trim($ar['MDS']));
            $msk = substr(trim($ar['MSK']), -4);
            $insert = 'INSERT INTO public.medstat_nsk_msk_table_matchings ( mds, msk ) VALUES ';
            $v[] = " ( '$mds' , '$msk' ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        $nskforms = \App\MedstatNskFormLink::all();
        $i = 0;
        foreach ($nskforms as $nskform) {
            $nsktables = \App\MedstatNskTableLink::OfForm($nskform->id)->get();
            foreach ($nsktables as $nsktable) {
                $ft = $nskform->form_name . $nsktable->tablen;
                //dd($ft);
                $matched = \App\MedstatNskMskTableMatching::OfMds($ft)->first();
                if ($matched) {
                    $nsktable->medstat_code = $matched->msk;
                    $nsktable->save();
                    $i++;
                }
            }
        }
        return $i;
    }*/

 /*   public function matchingRowMSCode($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        \App\MedstatNskMskRowMatching::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $mdstable = iconv('cp866', 'utf-8', trim($ar['MDSTABLE']));
            $mdsrow = $ar['MDSROW'];
            $mskrow = substr(trim($ar['MSKROW']), -3);
            $insert = 'INSERT INTO public.medstat_nsk_msk_row_matchings ( mdstable, mdsrow, mskrow ) VALUES ';
            $v[] = " ( '$mdstable', $mdsrow , '$mskrow' ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        $nskforms = \App\MedstatNskFormLink::all();
        $i = 0;
        foreach ($nskforms as $nskform) {
            $nsktables = \App\MedstatNskTableLink::OfForm($nskform->id)->get();
            foreach ($nsktables as $nsktable) {
                $nskrows = \App\MedstatNskRowLink::OfTable($nsktable->id)->get();
                $ft = $nskform->form_name . $nsktable->tablen;
                foreach ($nskrows as $nskrow) {
                    if ($nskrow) {
                        $matched = \App\MedstatNskMskRowMatching::OfMds($ft, $nskrow->row)->first();
                        if ($matched) {
                            $nskrow->medstat_code = $matched->mskrow;
                            $nskrow->save();
                            $i++;
                        }
                    }
                }

            }
        }
        return $i;
    }*/

    public function matchingColumnMSCode($dbf)
    {
        $db = dbase_open($dbf, 2);
        if (!$db) {
            new \Exception("Ошибка, не получается открыть базу данных $dbf");
        }
        dbase_pack($db);
        $numrecords = dbase_numrecords($db);
        \App\MedstatNskMskColumnMatching::truncate();
        $v = [];
        for ($i = 1; $i <= $numrecords; $i++) {
            $ar = dbase_get_record_with_names($db, $i);
            $mdstable = iconv('cp866', 'utf-8', trim($ar['MDSTABLE']));
            $mdscol = $ar['MDSCOL'];
            $mskcol = substr(trim($ar['MSKCOL']), -2);
            $transposed = $ar['INV'] ? 'TRUE' : 'FALSE';
            $insert = 'INSERT INTO public.medstat_nsk_msk_column_matchings ( mdstable, mdscol, mskcol, transposed ) VALUES ';
            $v[] = " ( '$mdstable', $mdscol , '$mskcol', $transposed ) ";
        }
        $values = implode(', ', $v );
        $res = \DB::insert($insert . $values);
        $nskforms = \App\MedstatNskFormLink::all();
        $i = 0;
        foreach ($nskforms as $nskform) {
            $nsktables = \App\MedstatNskTableLink::OfForm($nskform->id)->get();
            foreach ($nsktables as $nsktable) {
                //$nskcols = \App\MedstatNskColumnLink::OfTable($nsktable->id)->get();
                $ft = $nskform->form_name . $nsktable->tablen;
                $matched = \App\MedstatNskMskColumnMatching::FT($ft)->where('transposed', true)->groupBy(['mdstable', 'transposed'])->first(['mdstable', 'transposed']);
                //dd($matched);
                if ($matched) {
                    $nsktable->transposed = true;
                    $nsktable->save();
                    $i++;
                }
            }
        }
        return $i;
    }

    public function findTransposedTablesDisparity()
    {
        $tables = Table::with('form')->get();
        $disparity = [];
        foreach ($tables as $table) {
            $nsk_table = \App\MedstatNskTableLink::find($table->medstatnsk_id);
            if ($nsk_table) {
                if ((int)$nsk_table->transposed !== $table->transposed) {
                    $comment = $table->transposed ? 'В МФ таблица транспонирована' : 'В МС(НСК) таблица транспонирована';
                    $disparity[] = [ 'form_code' => $table->form->form_code, 'table_code' => $table->table_code, 'comment' => $comment ];
                }
            }
        }
        return $disparity;
    }

}
