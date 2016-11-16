<?php

namespace App\Http\Controllers\StatDataInput;

use App\Document;
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

        $i = "сравнение(меньшее(Ф32Т2120С16Г3, Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";

        //try {
            //$table = Table::find(10);
            $table = Table::find(113); // т. 3000 12 формы
            //$document = Document::find(7011);
            //$document = Document::find(7062);
            //$document = Document::find(7758); // 12 форма
            $document = Document::find(10634); // 32 форма
            $lexer = new ControlFunctionLexer($i);
            $parser = new ControlFunctionParser($lexer);
        $r = $parser->run();
        //dd($r);
            $interpret = new CompareControlInterpreter($r, $table);
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
