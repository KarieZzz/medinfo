<?php

namespace App\Http\Controllers\StatDataInput;

use App\Document;
use App\Medinfo\Lexer\DependencyControlInterpreter;
use App\Medinfo\Lexer\FoldControlInterpreter;
use App\Medinfo\Lexer\InterannualControlInterpreter;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use App\Medinfo\Control\TableDataCheck;
use App\Medinfo\Lexer\ControlFunctionLexer;
use App\Medinfo\Lexer\ControlFunctionParser;
use App\Medinfo\Lexer\CompareControlInterpreter;
use App\Table;


class DataCheckController extends Controller
{
    //

    public function check_table(Document $document, Table $table)
    {
        return TableDataCheck::execute($document, $table);
    }

    public function check_document(Document $document)
    {
        $form_protocol = [];
        $form_protocol['valid'] = true;
        $form_protocol['no_alerts'] = true;
        $form_protocol['no_data'] = true;
        $form_id = $document->form_id;
        $tables = Table::OfForm($form_id)->where('deleted', 0)->get();
        foreach ($tables as $table) {
            $offset = $table->table_code;
            $control = TableDataCheck::execute($document, $table);
            if ($control['no_data'] == false) {
                //dd($control);
                $form_protocol[$offset] = $control;
                $form_protocol['valid'] = $form_protocol['valid'] && $control['valid'];
                $form_protocol['no_alerts'] = $form_protocol['no_alerts'] && $control['no_alerts'];
                $form_protocol['no_data'] = false;
            }
        }
        return $form_protocol;
    }


    public function func_parser()
    {
        //$i = "сравнение(сумма(Ф32Т2120С1Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$i = "сравнение(сумма(С1Г3П0:С18Г3П0),  С22Г3П0, <=, группы(*), графы())";
        //$i = "сравнение(меньшее(С11, С16:С18, С20),  Ф32Т2120С22Г3, <=, группы(*), графы(3))";
        //$i = "сравнение(сумма(С11, С16:С18, С20) - сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3)- сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3),  Ф32Т2120С22Г3, <=, группы(*), графы(3))";
        //$i = "сравнение(сумма(Ф32Т2120С16Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        $i = "межгодовой(диапазон(С9Г4:С9Г16), 0)";
        //$i = "межгодовой(диапазон(С11Г3, С16Г3:С18Г3, С20Г3, С32Г3, С16Г3:С18Г3),  20)";
        //$i = "межгодовой(С1Г3+С4Г3, С1Г3,  20)";
        //$i = "кратность(диапазон(С01Г3:С02Г6),  .25)";
        //$i = "кратность(диапазон(С01Г3:С02Г6),  .25)";
        //$i = 'зависимость(Г3, сумма(Г4:Г8), группы(*), строки(*))';
        //$i = 'сравнение(Ф36-плТ2100С6Г4, Ф36-плТ2100С6Г4+Ф36-плТ2140С5Г4, >=, группы(первичные, село, !оп), графы())';
        //$i = 'сравнение(Ф36-плТ2100С6Г4, Ф36-плТ2100С6Г4+Ф36-плТ2140С5Г4, >=, группы(!юрлица), графы())';
        //$i = 'зависимость(Г3, Г4, группы(*),  строки(*))';

        //try {
            $table = Table::find(254); // т. 8000 30 формы
            //$table = Table::find(10);
            //$table = Table::find(115); // т. 2120 32 формы
            //$table = Table::find(950); // т. 2120 54 формы
            //$table = Table::find(948); // т. 2101 54 формы
            //$table = Table::find(420); // т. 2500 37 формы
            //$table = Table::find(179); // т. 2100 37 формы
            //$table = Table::find(5); // т. 2100 37 формы
            //$table = Table::find(113); // т. 3000 12 формы
            //$document = Document::find(7011);
            //$document = Document::find(7062);
            //$document = Document::find(7758); // 12 форма
            //$document = Document::find(12402); // 54 форма
            //$document = Document::find(10654); // 37 форма 2015
            //$document = Document::find(8429); // 37 форма 2015 - Ольхонская районная больница
            //$document = Document::find(9158); // 30 форма 2015 - Шелеховская районная больница
            $document = Document::find(15105); // 30 форма 2016 - ПАБ
            //$document = Document::find(7015); // 32 форма
            $lexer = new ControlFunctionLexer($i);
            $parser = new ControlFunctionParser($lexer);
            $r = $parser->run();
            //dd($r);
            //$interpret = new CompareControlInterpreter($r, $table);
            $interpret = new InterannualControlInterpreter($r, $table);
            //$interpret = new DependencyControlInterpreter($r, $table);
            //$interpret = new FoldControlInterpreter($r, $table);
            //dd($interpret);
            dd( $interpret->exec($document));
            $result = $interpret->exec($document) ? 'Правильно' : 'Ошибка';
            echo 'Результат выполнения контроля: ' . $result;
            //dd($interpret);
        //}
        //catch (\Exception $e) {
            //echo " Ошибка при обработке правила контроля " . $e->getMessage();
        //}

    }

    public function test_celllexer()
    {
        //$c = '0';
        //dd($c >= 'а' && $c <= 'я');
        //$input = '[ F30T1100, F12T2000, F121T1010, F162T3000 ]';
        //$input = '[ Ф30Т1100, Ф12Т2000, Ф121Т1010 ]';
        //$input = "сравнение(меньшее(С16, Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$input = "кратность(диапазон(С11Г3, С16Г3:С18Г3, С20Г3, С32Г3, С16Г3:С18Г3), .25 )";
        $input = "межгодовой(С11Г3, С16Г3,  20)";

        $lexer = new ControlFunctionLexer($input);
        $token = $lexer->nextToken();
        echo '<pre>';
        while($token->type != ControlFunctionLexer::EOF_TYPE) {
            echo $token . "\n";
            $token = $lexer->nextToken();
        }
        echo '</pre>';
        dd($lexer);
    }

}
