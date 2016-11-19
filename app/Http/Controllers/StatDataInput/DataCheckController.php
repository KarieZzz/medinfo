<?php

namespace App\Http\Controllers\StatDataInput;

use App\Document;
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
        //$i = "сравнение(сумма(С1Г3:С18Г3),  С22Г3, <=, группы(*), графы())";
        $i = "сравнение(меньшее(С11, С16:С18, С20),  Ф32Т2120С22Г3, <=, группы(*), графы(3))";
        //$i = "сравнение(сумма(С11, С16:С18, С20) - сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3)- сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3),  Ф32Т2120С22Г3, <=, группы(*), графы(3))";
        //$i = "сравнение(сумма(Ф32Т2120С16Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$i = "межгодовой(диапазон(С11Г3, С16Г3:С18Г3, С20Г3, С32Г3, С16Г3:С18Г3),  20)";

        //try {
            //$table = Table::find(10);
            $table = Table::find(115); // т. 2120 32 формы
            //$table = Table::find(113); // т. 3000 12 формы
            //$document = Document::find(7011);
            //$document = Document::find(7062);
            //$document = Document::find(7758); // 12 форма
            $document = Document::find(7015); // 32 форма
            $lexer = new ControlFunctionLexer($i);
            $parser = new ControlFunctionParser($lexer);
            $r = $parser->run();
            //dd($r);
            $interpret = new CompareControlInterpreter($r, $table);
            //$interpret = new InterannualControlInterpreter($r, $table);
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
        //$input = '[ F30T1100, F12T2000, F121T1010, F162T3000 ]';
        //$input = '[ Ф30Т1100, Ф12Т2000, Ф121Т1010 ]';
        $input = "сравнение(меньшее(С16, Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";

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
