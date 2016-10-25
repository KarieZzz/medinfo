<?php

namespace App\Http\Controllers\StatDataInput;

use App\Document;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

//use App\Medinfo\Lexer\Lexer;
use App\Medinfo\Lexer\ControlFunctionLexer;
use App\Medinfo\Lexer\ControlFunctionParser;
use App\Medinfo\Lexer\CompareControlInterpreter;


class DataCheckController extends Controller
{
    //
    public function func_parser()
    {
        $real = 'сравнение(
                    Ф30Т1100С1Г,
                    3.14 + сумма(Ф30Т1100С4Г:Ф30Т1100С45Г) + сумма(Ф30Т1100С48Г:Ф30Т1100С67Г) + Ф30Т1100С69Г + Ф30Т1100С71Г
                        + Ф30Т1100С73Г + сумма(Ф30Т1100С75Г:Ф30Т1100С96Г) + сумма(Ф30Т1100С101Г:Ф30Т1100С109Г) + сумма(Ф30Т1100С111Г:Ф30Т1100С122Г),
                    =,
                    группы(*),
                    графы(4,5 )
                )';
/*        $real = 'сравнение(
                    Ф30Т1100С1Г3,
                    3.14 + сумма(Ф30Т1100С4Г3:Ф30Т1100С45Г3) +  сумма(Ф30Т1100С48Г3:Ф30Т1100С67Г3) + Ф30Т1100С69Г3 + Ф30Т1100С71Г3
                        + Ф30Т1100С73Г3 + сумма(Ф30Т1100С75Г3:Ф30Т1100С96Г3) + сумма(Ф30Т1100С101Г3:Ф30Т1100С109Г3) + сумма(Ф30Т1100С111Г3:Ф30Т1100С122Г3),
                    =,
                    группы(*),
                    графы(*)
                )';*/
        //try {
            $table = 10;
            $document = Document::find(7011);
            $lexer = new ControlFunctionLexer($real);
            $parser = new ControlFunctionParser($lexer);
            $interpret = new CompareControlInterpreter($parser->controlFunction(), $document->form_id, $table);
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
        $input = '[ Ф30Т1100, Ф12Т2000, Ф121Т1010 ]';

        $lexer = new CellLexer($input);
        $token = $lexer->nextToken();
        echo '<pre>';
        while($token->type != CellLexer::EOF_TYPE) {
            echo $token . "\n";
            $token = $lexer->nextToken();
        }
        echo '</pre>';
        dd($lexer);
    }

}
