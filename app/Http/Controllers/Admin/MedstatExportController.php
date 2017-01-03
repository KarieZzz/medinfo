<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MedstatExportController extends Controller
{
    //
    public function msExport(int $document)
    {
        /*        $def = array(
                    array("date",     "D"),
                    array("name",     "C",  50),
                    array("age",      "N",   3, 0),
                    array("email",    "C", 128),
                    array("ismember", "L")
                );*/
        $document = \App\Document::find($document);
        //$document = \App\Document::find(10658); // 30 форма
        //$document = \App\Document::find(10634); // 32 форма
        $form = $document->form;
        $unit = $document->unit;

        if (is_null($unit)) {
            $unit = $document->unitgroup;
            $code = $unit->group_code;
        } else {
            $code = $unit->unit_code;
        }

        $tables = $form->tables->sortBy('table_index');

        $a1_code = '15'; // код отчетного года
        $a2_code = '1125'; // код Иркутской области
        $a4_code = $form->medstat_code . '00'; // код формы
        $offset = 4; // сдвиг до индекса массива, где начинаются данные ячеек

        $medstatsructure = [
            ["a1", "C", 2],
            ["a2", "C", 4],
            ["a4", "C", 7],
            ["a5", "C", 6],
            ["a6", "C", 3],
            ["a81", "N", 12, 2],
            ["a82", "N", 12, 2],
            ["a83", "N", 12, 2],
            ["a84", "N", 12, 2],
            ["a85", "N", 12, 2],
            ["a86", "N", 12, 2],
            ["a87", "N", 12, 2],
            ["a88", "N", 12, 2],
            ["a89", "N", 12, 2],
            ["a810", "N", 12, 2],
            ["a811", "N", 12, 2],
            ["a812", "N", 12, 2],
            ["a813", "N", 12, 2],
            ["a814", "N", 12, 2],
            ["a815", "N", 12, 2],
            ["a816", "N", 12, 2],
            ["a817", "N", 12, 2],
            ["a818", "N", 12, 2],
            ["a819", "N", 12, 2],
            ["a820", "N", 12, 2],
            ["a821", "N", 12, 2],
            ["a822", "N", 12, 2],
            ["a823", "N", 12, 2],
            ["a824", "N", 12, 2],
            ["a825", "N", 12, 2],
            ["a826", "N", 12, 2],
            ["a827", "N", 12, 2],
            ["a828", "N", 12, 2],
            ["srt", "C", 25],
            ["n1", "N", 2, 0],
            ["n2", "N", 2, 0],
        ];

        $insert_pattern_no_works = [
            'a1' => $a1_code,
            'a2' => $a2_code,
            'a4' => $a4_code,
            'a5' => '',
            'a6' => '',
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => 0,
            12 => 0,
            13 => 0,
            14 => 0,
            15 => 0,
            16 => 0,
            17 => 0,
            18 => 0,
            19 => 0,
            20 => 0,
            21 => 0,
            22 => 0,
            23 => 0,
            24 => 0,
            25 => 0,
            26 => 0,
            27 => 0,
            28 => 0,
            'srt' => '',
            'n1' => 0,
            'n2' => 0,
        ];

        $insert_pattern = [
            $a1_code, //0
            $a2_code, // 1
            $a4_code, // 2
            '', // 3 - код таблицы
            '', //4 - код строки
            0, //5  - первая графа
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            '',
            0,
            0,
        ];

        // создаем
        //$db = dbase_create('/home/vagrant/Code/m.dbf', $medstatsructure);
        //dd(storage_path('app/exports/medstat') . '/m.dbf');
        $dbf_file = storage_path('app/exports/medstat') . '/' . $code . '_' . $form->form_code . '.dbf';
        $db = dbase_create($dbf_file, $medstatsructure);
        if (!$db) {
            echo "Ошибка, не получается создать базу данных m.dbf\n";
        }
        //dbase_add_record($db, $test_array);
        //dd(dbase_get_header_info($db));

        foreach ($tables as $table) {
            $rows = \App\Row::OfTable($table->id)->InMedstat()->get();
            if (!$table->transposed ) {
                foreach ($rows as $row) {
                    $insert_data = $insert_pattern;
                    if (\App\Cell::OfDTR($document->id, $table->id, $row->id)->sum('value')) {
                        $cells = \App\Cell::OfDTR($document->id, $table->id, $row->id)->get();
                        $insert_data[3] = '00' . $table->medstat_code;
                        $insert_data[4] = $row->medstat_code;
                        foreach ($cells as $cell) {
                            $insert_data[(int)$cell->column->medstat_code + $offset] = (float)$cell->value;
                        }
                        try {
                            dbase_add_record($db, $insert_data);
                        }
                        catch ( \ErrorException $e) {
                            dd($insert_data);
                        }
                    }
                }
            } elseif ($table->transposed == 1) {
                $insert_data = $insert_pattern;
                $insert_data[3] = '00' . $table->medstat_code;
                $insert_data[4] = '001';
                if (\App\Cell::OfDocumentTable($document->id, $table->id)->sum('value')) {
                    $cells = \App\Cell::OfDocumentTable($document->id, $table->id)->get();
                    foreach ($cells as $cell) {
                        $insert_data[(int)$cell->row->medstat_code + $offset] = (float)$cell->value;
                    }
                    try {
                        dbase_add_record($db, $insert_data);
                    }
                    catch ( \ErrorException $e) {
                        dd($insert_data);
                    }

                }
            }
        }
        return response()->download($dbf_file);
    }

}
