<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MedstatExportController extends Controller
{
    //
    public function test_export()
    {
        $def = array(
            array("date",     "D"),
            array("name",     "C",  50),
            array("age",      "N",   3, 0),
            array("email",    "C", 128),
            array("ismember", "L")
        );

        $a1_code = '16'; // код отчетного года
        $a2_code = '1125'; // код Иркутской области

        $medstatsructure = [
            ["a1",     "C",  2],
            ["a2",     "C",  4],
            ["a4",     "C",  7],
            ["a5",     "C",  6],
            ["a6",     "C",  3],
            ["a81",    "N",  12, 2],
            ["a82",    "N",  12, 2],
            ["a83",    "N",  12, 2],
            ["a84",    "N",  12, 2],
            ["a85",    "N",  12, 2],
            ["a86",    "N",  12, 2],
            ["a87",    "N",  12, 2],
            ["a88",    "N",  12, 2],
            ["a89",    "N",  12, 2],
            ["a810",   "N",  12, 2],
            ["a811",   "N",  12, 2],
            ["a812",   "N",  12, 2],
            ["a813",   "N",  12, 2],
            ["a814",   "N",  12, 2],
            ["a815",   "N",  12, 2],
            ["a816",   "N",  12, 2],
            ["a817",   "N",  12, 2],
            ["a818",   "N",  12, 2],
            ["a819",   "N",  12, 2],
            ["a820",   "N",  12, 2],
            ["a821",   "N",  12, 2],
            ["a822",   "N",  12, 2],
            ["a823",   "N",  12, 2],
            ["a824",   "N",  12, 2],
            ["a825",   "N",  12, 2],
            ["a826",   "N",  12, 2],
            ["a827",   "N",  12, 2],
            ["a828",   "N",  12, 2],
            ["srt",    "C",  25],
            ["n1",     "N",  2,  0],
            ["n2",     "N",  2,  0],

        ];

// создаем
        if (!dbase_create('/home/vagrant/Code/m.dbf', $def)) {
            echo "Ошибка, не получается создать базу данных\n";
        }
    }

}
