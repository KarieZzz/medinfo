<?php

namespace App\Http\Controllers\Tests;

use App\Medinfo\DSL\CompareTranslator;
use App\Medinfo\DSL\ControlFunctionEvaluator;
use App\Medinfo\DSL\EquationFunctionParser;
use App\Unit;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Medinfo\DSL\ControlFunctionLexer;
use App\Medinfo\DSL\ControlFunctionParser;
use App\Medinfo\DSL\ControlPtreeTranslator;
use App\Medinfo\DSL\Translator;
use App\Medinfo\DSL\Evaluator;

use App\Document;
use App\Table;
use App\CFunction;

class LexerParserController extends Controller
{
    //
    public function lexerTest()
    {
        //$i = "сравнение(сумма(Ф32Т2120С1Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$i = "сравнение(сумма(С1Г3П0:С18Г3П0),  С22Г3П0, <=, группы(*), графы(*))";
        //$i = "сравнение(меньшее(С11, С16:С18, С20),  Ф32Т2120С22Г3, <=, группы(*), графы(3-9,15))";
        //$i = "сравнение(сумма(Ф32Т2110С1Г3П0:Ф32Т2110С1Г5П0, С16:С18, С6, С8, С20) - сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3)- сумма(Ф30Т1100С22Г3, Ф30Т1100С25Г3:Ф30Т1100С28Г3),
          //Ф32Т2120С22Г3, <=, группы(*), графы(*))";
        //$i = "сравнение(сумма(Ф32Т2120С16Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$i = 'сравнение(Ф36-плТ2100С6Г4, Ф36-плТ2100С6Г4+Ф36-плТ2140С5Г4, >=, группы(первичные, село, !оп), графы())';
        //$i = 'сравнение(Ф36-плТ2100С6Г4, Ф36-плТ2100С6Г4+Ф36-плТ2140С5Г4, >=, группы(!юрлица), графы())';
        //$i = "сравнение(С1, С6, >=, группы(*), графы(*))";
        //$i = "сравнение(С1, С6, >=, группы(*))";
        //$i = "сравнение((сумма(С1, С2, С16Г3:С18Г5, С20)+С31+С41)/2, С6, >=)";
        //$i = "сравнение((сумма(Г4, Г9:Г11, Г13)+Г16)/2, Г15, >=, группы(село, !сводные , !север, !юл), строки(1.0,5.4,7.0-18.0))";
        //$i = "сравнение((Ф201Т1000С2Г4ПI + сумма(Ф201Т1000С1Г4ПI, Ф201Т1000С1Г9:Ф201Т1000С1Г11, Ф201Т1000С10Г13)+Ф201Т1000С2Г15П-1)/2, Ф37Т2100С01Г15П-1, >=)";
        // проверка смешанного контроля, со ссылками на ячейки из разных периодов
        //$i = "сравнение(Ф201Т1000С3.1Г3П-1 + Ф201Т1000С1.1Г3 - Ф201Т1000С2.1Г3, Ф201Т1000С3.1Г3, =, группы(!село, пi, !пii))";
        //$i = "сравнение(С1, С1.1+С1.2, =)";
        //$i = "сравнение(Г6, сумма(Г6.1:Г6.7), =)";
        //$i = "сравнение(С11, С01+С02+сумма(С06:С10), =)";

        //$i = "сравнение(Ф12Т2000С1.0Г15ПII+ + Ф12Т2000С1.0Г8 - Ф12Т2000С1.0Г14, Ф12Т2000С1.0Г15 , =)";
        //$i = "сравнение(Г15П-1 + Г8 - Г14, Г15 , =, строки(1.0-3.0))";
        //$i = "сравнение(С1, С6, ==,группы(),строки())";
        //$i = "сравнение(С5.8Г16, С5.8Г15, =)";
        //$i = "сравнение(С4.2.1Г13, С4.2.1Г9, =, группы(*), строки())";
        /*$i = "сравнение(
                большее(Г4:Г9), 
                Г10, 
                =, 
                строки(1.0, 2.2, 4.1.1, 5.2, 5.2.1, 5.2.2, 7.1, 7.1.1, 7.1.2, 7.5.1, 7.8.1, 7.8.2, 7.9.1, 8.8, 
                    10.1, 10.2, 10.4.2, 10.4.3, 10.6.1, 10.6.2, 10.6.3, 10.6.4, 10.6.5, 10.8.2, 
                    11.3, 12.1, 12.5.1, 10.5.1, 10.5.2, 10.5.3
                )
            )";*/
        //$i = "сравнение(С8.0, С8.1+С8.2+С8.3+С8.4+С8.5+С8.6+С8.7+С8.8+С8.9+С8.10+С8.11+С8.12, >=))";
        //$i = "сравнение(С6.0Г4-С6.1Г4, Ф10Т2000С1Г7, >=)";
        //$i = "сравнение(Г3, Г4, =)";
        //$i = "сравнение(С03Г4, Ф30Т1100С1Г9, =)";
        //$i = 'сравнение(С10.5, С10.5.1+С10.5.2+С10.5.3+С10.5.4, >=, группы(сводные, юл), графы(4,7))';
        //$i = "сравнение(С1, С69+сумма(С4:С45)+сумма(С48:С67)+С71+С73+сумма(С75:С96)+сумма(С101:С109)+сумма(С111:С122), =, группы(!музот), графы(3-5,7-9))";
        //$i = "сравнение(С1, С5+сумма(С6:С45)+сумма(С48:С67)+С69+С71+С73+сумма(С75:С96)+сумма(С101:С109)+сумма(С112:С122), =)";
        //$i = "сравнение(Г4, сумма(Г6:Г8), =, группы(!музот), строки(3-43, 45-82, 84-90, 92-123, 127-135, 144-155, 159, 163, 170-173, 175-194, 198, 203, 204, 206, 208, 209, 213-218, 220))";
        //$i = "сравнение(С22, сумма(С22.1:С22.8)+сумма(С22.9:С22.11), =, группы(*), графы(*))";
        //$i = "сравнение(С1Г3, С2Г3, =)";
        //$i= "сравнение(С214Г3-С214Г5-С214Г7, Т1105С1Г6, =, группы(!музот))";
        //$i= "сравнение(Г4, Т3000СГ4, <=)";
        //$i= "сравнение(С1.0, Т3000С2.0+С3.0+С4.0+С5.0+С6.0+С7.0+С8.0+С9.0+С10.0+С11.0+С12.0+С13.0+С14.0+С15.0+С16.0+С18.0+С19.0+С20.0, =)";

        //$i = "зависимость(Г4, Г16, группы(оп), строки(*))";
        //$i = 'зависимость(Г3, сумма(Г4:Г8))';
        //$i = 'зависимость(Г3, Г4, группы(сводные))';

/*        // межпериодные
        $i = "межгодовой(С1.0Г15+С1.0Г14-С1.0Г10,  С1.0Г10+С1.0Г11, 0)";
        //$i = "межгодовой(С1Г3+С4Г3, С1Г3,  20)";

        //$i = "мгдиапазон(диапазон(С01Г3:С02Г6), 20)";
        //$i = "мгдиапазон(диапазон(С16Г3:С18Г3), 0.2)";
        //$i = "мгдиапазон(диапазон(С11Г3, С16Г3:С18Г3, С20Г3, С32Г3, С16Г3:С18Г3),  20)";
        //$i = "мпдиапазон(диапазон(С3Г3:С3.2Г15), >=, группы(!пi))";*/

        //$i = "кратность(диапазон(С01Г3:С02Г6),  0.25)";
        //$i = "кратность(диапазон(С1Г3:С221Г8), 0.25 )";
        //$i = "кратность(диапазон(С01Г3:С02Г6),  .25)";

        //$i = '(a2 - a1)/a2 * 100 > a3';
        //$i = "сравнение(С1.0Г4+Т2000С1.0Г4, Ф14Т2000С1.0Г22, =)";
        $i = "сравнение(С1.0, С2.0+С3.0+С4.0+С5.0+С6.0+С7.0+С8.0+С9.0+С10.0+С11.0+С12.0+С13.0+С14.0+С15.0+С17.0+С18.0+С19.0+С20.0, =)";
        //$i = "сравнение(Г8, Г10, =)";

        //$cellcount = preg_match_all('/Ф([а-я0-9.-]+)Т([\w.-]+)С([\w.-]+)Г(\d{1,})/u', $i, $matches, PREG_SET_ORDER);
        //$res = preg_match('|(?:\()(.*?),(.*?)\)|usei', $i, $matches);
        //$res = preg_match_all('/\((.*?)\(/u', $i, $matches, PREG_SET_ORDER);
        //dd($matches);

         $lexer = new ControlFunctionLexer($i);
         $tockenstack = $lexer->getTokenStack();
        //dd($tockenstack);
        //dd($lexer->normalizeInput());
        //dd($lexer);

         $parser = new ControlFunctionParser($tockenstack);
         $parser->func();
        //dd($parser);
        //dd($parser->root);
        //dd($parser->function_index);
        //dd(json_decode(json_encode($parser->root)));
        //dd($parser->celladressStack);
        //dd($parser->cellrangeStack);
        //dd($parser->argStack);

        //$table = Table::find(10);     // Ф30 Т1100
        //$table = Table::find(15);     // Ф30 Т2100
        //$table = Table::find(50);     // Ф30 Т5117
        //$table = Table::find(252);    // Ф30 Т5301
        $table = Table::find(111);      // Ф12 Т1000
        //$table = Table::find(112);    // Ф12 Т2000
        //$table = Table::find(441);    // Ф12 Т4000
        //$table = Table::find(115);    // Ф32 Т2120
        //$table = Table::find(179);    // Ф37 Т2100
        //$table = Table::find(151);    // Ф41 Т2100
        //$table = Table::find(2);      // Ф47 Т0100
        //$table = Table::find(992);    // Ф201 Т1000

        $translator = Translator::invoke($parser, $table);
        //dd($translator);
        //$translator = new ControlPtreeTranslator($parser, $table);
        //$translator = new CompareTranslator($parser, $table);
        //$translator->setParentNodesFromRoot();
        //$translator->parseCellAdresses();
        //$translator->parseCellRanges();
        //$translator->validateVector();
        $translator->prepareIteration();
        //dd($translator);
        //dd($translator->getProperties());
        //dd($translator->parser->root);
        //echo (json_encode($translator->parser->root, JSON_PARTIAL_OUTPUT_ON_ERROR));
        //echo json_last_error();
        //dd(json_decode(json_encode($translator->parser->root, JSON_PARTIAL_OUTPUT_ON_ERROR, JSON_FORCE_OBJECT), TRUE));
        //dd((array)$translator->parser->root);
        //dd(unserialize(serialize($translator->parser->root)));
        //dd($translator->parser->argStack);
        //dd($translator->parser->rcRangeStack);
        //dd($translator->parser->rcStack);
        //dd($translator->parser->excludeGroupStack);
        //dd($translator->scriptReadable);
        //dd($translator->iterations);
        //dd(json_decode(json_encode($translator->iterations), TRUE));
        //dd($translator->type);
        //dd($translator->getProperties());
// Запуск из десериализованного объекта pTree, сохраненного в БД
        //$cfunc = CFunction::find(2652); // сравнение(С8.0, С8.1+С8.2+С8.3+С8.4+С8.5+С8.6+С8.7+С8.8+С8.9+С8.10+С8.11+С8.12, >=)) ф. 12 т. 2000
        //$cfunc = CFunction::find(3280);
        //dd($cfunc);
        //$pTree = unserialize(base64_decode($cfunc->ptree));
        //dd($pTree);
        //$props = json_decode($cfunc->properties, true);
        //dd($props);
        //dd($iterations);

        $document = Document::find(47884); // 12 ф Все организации 3 кв. МСК
        //$document = Document::find(16845); // 12 ф ГКБ№8 за 2017 год
        //$document = Document::find(12269); // 12 ф Все организации 2016 год
        //$document = Document::find(13753); // 41 ф ДР1 за 2016 год
        //$document = Document::find(12657); // 30 ф РБ Слюдянка за 2016 год
        //$document = Document::find(12268); // 30 ф Свод за 2016 год
        //$document = Document::find(19251); // 47 ф за 2017 год
        //$document = Document::find(19264); // 201 ф за 1 квартал 2018 года (наркология)
        //$document = Document::find(19265); // 201 ф за IV квартал 2017 года (наркология)
        //$document = Document::find(16434); // 37 ф за 2017 год ИОПНД
        //$document = Document::find(19272); // 37 ф за I квартал 2018 года ИОПНД
        //$document = Document::find(2046);       // 12село ф за 2017 год Волоколамская ЦРБ
        $evaluator = Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        //$evaluator = new ControlFunctionEvaluator($translator->parser->root, $translator->getProperties(), $document);
        //$evaluator = new ControlFunctionEvaluator($pTree, $props, $document);

        //$evaluator->prepareCellValues();
        //$evaluator->prepareCAstack();
        //dd($evaluator->arguments);
        //dd($evaluator->pTree);
        //dd($evaluator->caStack);
        //dd($evaluator->iterations);

        //return $evaluator->evaluate();

        //dd($evaluator->makeControl());
        $evaluator->makeControl();
        //dd($evaluator);
        //return ($evaluator->iterations);
        //return ($evaluator->properties);
        //return $evaluator->makeControl();
        return $evaluator->makeControl();
        //dd($evaluator->pTree);
    }

    public function testCalculation()
    {
        //$rule = "счетмо()";
        //$rule = "счетмо(список(u47_100_03))";
        $rule = "расчет(Ф30Т1001С3Г4+Ф30Т1001С13Г4+Ф30Т1001С19Г4+Ф30Т1001С28Г4+Ф30Т1001С86Г4+Ф30Т1001С88Г4+Ф30Т1001С131Г4+Ф30Т1001С132Г4, список(u47_100_19))";
        $table = Table::find(2);
        $document = Document::find(19251);
        $lexer = new \App\Medinfo\DSL\ControlFunctionLexer($rule);
        $tockenstack = $lexer->getTokenStack();
        $parser = new \App\Medinfo\DSL\ControlFunctionParser($tockenstack);
        $parser->func();
        $translator = \App\Medinfo\DSL\Translator::invoke($parser, $table);
        $translator->prepareIteration();
        $evaluator = \App\Medinfo\DSL\Evaluator::invoke($translator->parser->root, $translator->getProperties(), $document);
        $evaluator->makeConsolidation();
        //dd($evaluator->calculationLog);
        foreach ($evaluator->calculationLog as &$el) {
            $unit = Unit::find($el['unit_id']);
            $el['unit_name'] = $unit->unit_name;
            $el['unit_code'] = $unit->unit_code;
        }

        $log_initial = collect($evaluator->calculationLog);
        //$log_sorted = $log_initial->sortBy('unit_code');
        $log_c_sorted = $log_initial->sortBy('unit_code');
        //dd($log);
        $log_sorted = [];
        foreach ($log_c_sorted as $el ) {
            $log_sorted[] = $el;
        }
        //dd($log_sorted);

        //echo(json_encode($log->toArray()));
        $log = json_encode($log_sorted);
        echo $log;
        //return $evaluator->evaluate();
        //$evaluator->evaluate();
        //return view('reports.consolidationLog', compact('log'));
    }

    public function func_parser()
    {
        //$i = "сравнение(сумма(Ф32Т2120С1Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$i = "сравнение(сумма(С1Г3П0:С18Г3П0),  С22Г3П0, <=, группы(*), графы())";
        //$i = "сравнение(меньшее(С11, С16:С18, С20),  Ф32Т2120С22Г3, <=, группы(*), графы(3))";
        //$i = "сравнение(сумма(С11, С16:С18, С20) - сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3)- сумма(Ф30Т1100С11Г3, Ф30Т1100С13Г3:Ф30Т1100С15Г3),  Ф32Т2120С22Г3, <=, группы(*), графы(3))";
        //$i = "сравнение(сумма(Ф32Т2120С16Г3:Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$i = "межгодовой(диапазон(С9Г4:С9Г16), 0)";
        //$i = "зависимость(Г3, Г4+Г5, группы(оп), строки(*))";
        //$i = "сравнение(С1, С6, >=, группы(*), графы(*))";
        $i = "межгодовой(С1.0Г15+С1.0Г14-С1.0Г8,  С1.0Г15, 20)";

        //$i = "межгодовой(диапазон(С11Г3, С16Г3:С18Г3, С20Г3, С32Г3, С16Г3:С18Г3),  20)";
        //$i = "межгодовой(С1Г3+С4Г3, С1Г3,  20)";
        //$i = "кратность(диапазон(С01Г3:С02Г6),  .25)";
        //$i = "кратность(диапазон(С01Г3:С02Г6),  .25)";
        //$i = 'зависимость(Г3, сумма(Г4:Г8), группы(*), строки(*))';
        //$i = 'сравнение(Ф36-плТ2100С6Г4, Ф36-плТ2100С6Г4+Ф36-плТ2140С5Г4, >=, группы(первичные, село, !оп), графы())';
        //$i = 'сравнение(Ф36-плТ2100С6Г4, Ф36-плТ2100С6Г4+Ф36-плТ2140С5Г4, >=, группы(!юрлица), графы())';
        //$i = 'зависимость(Г3, Г4, группы(*),  строки(*))';
        //try {
        //$table = Table::find(254); // т. 8000 30 формы
        //$table = Table::find(4); // т. 1001 30 формы
        //$table = Table::find(980); // т. 2710 30 формы
        //$table = Table::find(10);
        //$table = Table::find(115); // т. 2120 32 формы
        //$table = Table::find(950); // т. 2120 54 формы
        //$table = Table::find(948); // т. 2101 54 формы
        //$table = Table::find(420); // т. 2500 37 формы
        //$table = Table::find(179); // т. 2100 37 формы
        //$table = Table::find(5); // т. 2100 37 формы
        $table = Table::find(113); // т. 3000 12 формы
        //$document = Document::find(7011);
        //$document = Document::find(7062);
        //$document = Document::find(7758); // 12 форма
        //$document = Document::find(12402); // 54 форма
        //$document = Document::find(10654); // 37 форма 2015
        //$document = Document::find(8429); // 37 форма 2015 - Ольхонская районная больница
        //$document = Document::find(9158); // 30 форма 2015 - Шелеховская районная больница
        //$document = Document::find(15105); // 30 форма 2016 - ПАБ
        //$document = Document::find(11433); // 30 форма 2016 - Листвянка
        //$document = Document::find(12268); // 30 форма 2016 - все
        $document = Document::find(13425); // 12 форма 2016 ГБ3 Братск
        //$document = Document::find(7015); // 32 форма
        //$lexer = new ControlFunctionLexer($i);

        //$parser = new ControlFunctionParser($lexer);
        //$r = $parser->run();
        //dd($r);
        //$interpret = new CompareControlInterpreter($r, $table);
        //$interpret = new InterannualControlInterpreter($r, $table);
        //$interpret = new DependencyControlInterpreter($r, $table);
        //$interpret = new FoldControlInterpreter($r, $table);
        //dd($interpret);
        //dd( $interpret->exec($document));
        //$result = $interpret->exec($document) ? 'Правильно' : 'Ошибка';
        //echo 'Результат выполнения контроля: ' . $result;
        //dd($interpret);
        //}
        //catch (\Exception $e) {
        //echo " Ошибка при обработке правила контроля " . $e->getMessage();
        //}

    }

    public function test_making_AST()
    {
        $input = "С11Г13 + С12Г15 - 40 - 23/2";
        //$input = "20-23/2+40";
        //$input = "((20+23))/2-40";
        //$input = "20*3+40/2+50+60";
        //$input = "20*3/2*100";
        //$input = "7 + 3 * (10 / (12 / (3 + 1) - 1))";
        //$input = "7 + 3 * (10 / (12 / (3 + 1) - 1)) / (2 + 3) - 5 - 3 + (8)";
        //$input = "7 + (((3 + 2)))";
        //$input = "20 + 10/2 + 3*6";
        //echo eval("return $input;");
        $lexer = new \App\Medinfo\DSL\CalculationFunctionLexer($input);
        $tokenstack = $lexer->getTokenStack();
        dd($lexer);
        //$tokenstack->rewind();
        //dd($tokenstack);
        $parcer = new \App\Medinfo\DSL\CalculationFunctionParser($tokenstack);
        $parcer->expression();
        //dd($parcer->argStack);
        $eval = new \App\Medinfo\DSL\EvaluatorExample($parcer->expression());
        //dd($eval->evaluate());
    }

    public function test_making_AST_w_bool()
    {
        $input = "33 + 44 - 40 > 23/2";
        $lexer = new ControlFunctionLexer($input);
        $tockenstack = $lexer->getTokenStack();
        //dd($tockenstack);
        $parser = new EquationFunctionParser($tockenstack);
        dd($parser->equation());
    }

    public function test_celllexer()
    {
        //$c = '0';
        //dd($c >= 'а' && $c <= 'я');
        //$input = '[ F30T1100, F12T2000, F121T1010, F162T3000 ]';
        //$input = '[ Ф30Т1100, Ф12Т2000, Ф121Т1010 ]';
        //$input = "сравнение(меньшее(С16, Ф32Т2120С18Г3),  Ф32Т2120С22Г3, >=, группы(*), графы())";
        //$input = "кратность(диапазон(С11Г3, С16Г3:С18Г3, С20Г3, С32Г3, С16Г3:С18Г3), .25 )";
        //$input = "межгодовой(С11Г3, С16Г3,  20)";
        $input = "20+Г3-Г5/2";

        //$lexer = new ControlFunctionLexer($input);
        $lexer = new \App\Medinfo\DSL\CalculationFunctionLexer($input);
        $token = $lexer->nextToken();
        echo '<pre>';
        while($token->type != \App\Medinfo\DSL\CalculationFunctionLexer::EOF_TYPE) {
            echo $token . "\n";
            $token = $lexer->nextToken();
        }
        echo '</pre>';
        //foreach ($lexer->tokenstack->stack as $item) {
        //  var_dump($lexer->getTokenType($item->type));
        //}
        $t = $lexer->getTokenStack();
        $t->rewind();
        while ($t->valid()) {
            echo $t->key(), $t->current(), PHP_EOL;
            $t->next();
        }

        //$lexer->tokenstack->stack->top();
        //$lexer->tokenstack->stack->rewind();
        //dd($lexer->tokenstack->stack->valid());
        //echo($lexer->tokenstack->stack->key());
    }

    public function batchRename()
    {
        $cfunctions = CFunction::all();
        $i = 0;
        foreach ($cfunctions as $cfunction) {
            $remove = array(", группы(*), строки(*)", ", группы(*), графы(*)", ", группы(*), строки()", ", группы(*), графы()");
            $upd = str_replace($remove, "", $cfunction->script);
            $cfunction->script = $upd;
            $cfunction->save();
            $i++;
        }
        return 'Обработано функций: ' . $i;

    }

}
