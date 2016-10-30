<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 30.10.2016
 * Time: 12:51
 */

namespace App\Medinfo\Control;

use App\CFunction;
use App\Medinfo\ControlHelper;
use App\Document;
use App\Table;
use App\Medinfo\Lexer\ControlFunctionLexer;
use App\Medinfo\Lexer\ControlFunctionParser;
use App\Medinfo\Lexer\CompareControlInterpreter;

class TableDataCheck
{

    public static function execute(Document $document, Table $table)
    {
        if (ControlHelper::tableContainsData($document->id, $table->id)) {
            $table_protocol['no_data'] = false;
            $cfunctions = CFunction::OfTable($table->id)->Active()->get();
            if (count($cfunctions) == 0) {
                $table_protocol['no_rules'] = true;
                return $table_protocol;
            }
            $rules =  &$table_protocol['rules'];
            foreach ($cfunctions as $function) {

                $lexer = new ControlFunctionLexer($function->script);
                $parser = new ControlFunctionParser($lexer);
                $r = $parser->run();
                $interpret = new CompareControlInterpreter($r, $table);
                $rules[] = $interpret->exec($document);
            }
            return $table_protocol;
        }
        $table_protocol['no_data'] = true;
        return $table_protocol;
    }
}