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
use PhpParser\Comment\Doc;


class DataCheckController extends Controller
{
    //

    public function check_table(Document $document, Table $table)
    {
        return TableDataCheck::execute($document, $table);
    }

    public function check_document(Document $document)
    {

    }


    public function func_parser()
    {
        $real = 'сравнение(
                    Ф30Т1100С1Г3,
                    сумма(Ф30Т1100С4Г:Ф30Т1100С45Г) +сумма(Ф30Т1100С48Г:Ф30Т1100С67Г) + Ф30Т1100С69Г + Ф30Т1100С71Г
                        + Ф30Т1100С73Г + сумма(Ф30Т1100С75Г:Ф30Т1100С96Г) + сумма(Ф30Т1100С101Г:Ф30Т1100С109Г) + сумма(Ф30Т1100С111Г:Ф30Т1100С122Г),
                    =,
                    группы(*),
                    графы(*))';
        //$i = "сравнение(Ф16-внТ1000С01Г5 +Ф16-внТ1000С02Г5,50.00 + сумма(ФТС4Г:ФТС45Г) +сумма(ФТС48Г:ФТС67Г) + ФТС69Г + ФТС71Г + ФТС73Г + сумма(ФТС75Г:ФТС96Г) + сумма(ФТС101Г:ФТС109Г) + сумма(ФТС111Г:ФТС122Г), =, группы(*), графы(*))";
        //$i = "сравнение( ФТС1Г, сумма(ФТС4Г:ФТС45Г) +сумма(ФТС48Г:ФТС67Г) + ФТС69Г + ФТС71Г + ФТС73Г + сумма(ФТС75Г:ФТС96Г) + сумма(ФТС101Г:ФТС109Г) + сумма(ФТС111Г:ФТС122Г), =, группы(*), графы(*))";
        //$i = "сравнение( ФТС1Г, сумма(ФТС4Г:ФТС7Г), =, группы(*), графы(11,12,13, 7-10) )";
        //$i = "сравнение( ФТС67Г,+ФТС68Г,>=,группы(*), графы(3-9))";
        $i = "сравнение(ФТСГ3, ФТСГ5+ФТСГ7,=, группы(*), строки(10-20))";

/*        $real = 'сравнение(
                    Ф30Т1100С1Г3,
                    3.14 + сумма(Ф30Т1100С4Г3:Ф30Т1100С45Г3) +  сумма(Ф30Т1100С48Г3:Ф30Т1100С67Г3) + Ф30Т1100С69Г3 + Ф30Т1100С71Г3
                        + Ф30Т1100С73Г3 + сумма(Ф30Т1100С75Г3:Ф30Т1100С96Г3) + сумма(Ф30Т1100С101Г3:Ф30Т1100С109Г3) + сумма(Ф30Т1100С111Г3:Ф30Т1100С122Г3),
                    =,
                    группы(*),
                    графы(*)
                )';*/
        //try {
            $table = Table::find(10);
            //$document = Document::find(7011);
            $document = Document::find(7062);
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
