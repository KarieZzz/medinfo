<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 30.10.2016
 * Time: 12:51
 */

namespace App\Medinfo\Control;

use App\CFunction;
use App\Document;
use App\Table;
use App\Medinfo\ControlHelper;
use App\Medinfo\Lexer\ControlFunctionLexer;
use App\Medinfo\Lexer\ControlFunctionParser;
//use App\Medinfo\Lexer\CompareControlInterpreter;
use App\Medinfo\Lexer\FunctionDispatcher;

class DataCheck
{

    public static function tableControl(Document $document, Table $table, $forcereload = 0)
    {
        set_time_limit(240);
        if (ControlHelper::CashedProtocolActual($document->id, $table->id) && !$forcereload) {
            $table_protocol = ControlHelper::loadProtocol($document->id, $table->id);
            return $table_protocol;
        }
        if (ControlHelper::tableContainsData($document->id, $table->id)) {
            $table_protocol['no_data'] = false;
            $table_protocol['table_id'] = $table->id;
            $cfunctions = CFunction::OfTable($table->id)->Active()->get();
            if (count($cfunctions) == 0) {
                $table_protocol['no_rules'] = true;
                $table_protocol['valid'] = true;
                $table_protocol['no_alerts'] = true;
                return $table_protocol;
            }
            $table_protocol['no_rules'] = false;
            $table_protocol['errors'] = [];
            $rules = &$table_protocol['rules'];
            $valid = true;
            $do_not_alerted = true;
            foreach ($cfunctions as $function) {
                try {
                    $interpreter = self::cacheOrCompile($function, $table);
                    //dd($interpreter);
                    $rule = $interpreter->exec($document);
                    $rule['function_id'] = $interpreter->functionIndex;
                    $rule['function'] = FunctionDispatcher::$structNames[$interpreter->functionIndex];
                    $rule['level'] = $function->level;
                    $rule['input'] = $function->script;
                    $rule['comment'] = $function->comment;
                    if ($rule['not_in_scope']) {
                        $rule['comment'] .= " Правило контроля не применяется к данному документу (ограничения по группе медицинских организаций)";
                    }
                    if (isset($rule['errors'])) {
                        foreach($rule['errors'] as $error) {
                            $table_protocol['errors'][] =  $error;
                        }
                    }
                    $rule['no_rules'] = false;
                    $rules[] = $rule;
                    // При проверке валидности данных по таблице учитываем только скрипты уровня "ошибка"
                    if ($function->level == 1) {
                        $valid = $valid && $rule['valid'];
                    } elseif ($function->level == 2) {
                        $do_not_alerted = $do_not_alerted && $rule['valid'];
                    }

                }
                catch (\Exception $e) {
                    $rules[] = ['error' => "<strong class='text-danger'>Ошибка при обработке правила контроля:</strong> <code>" . $function->script . '</code> ' . $e->getMessage() ];
                }
            }
            $table_protocol['valid'] = $valid;
            $table_protocol['no_alerts'] = $do_not_alerted;
            ControlHelper::cashProtocol($table_protocol, $document->id, $table->id);
            return $table_protocol;
        }
        $table_protocol['no_data'] = true;
        return $table_protocol;
    }

    public static function cacheOrCompile(CFunction $function, Table $table)
    {
        //$fo = unserialize(base64_decode($function->compiled_cashe));
        //if (!$fo) {
            $lexer = new ControlFunctionLexer($function->script);
            $parser = new ControlFunctionParser($lexer);
            $r = $parser->run();
            $callInterpreter = FunctionDispatcher::INTERPRETERNS . FunctionDispatcher::$interpreterNames[$parser->functionIndex];
            $interpreter = new $callInterpreter($r, $table, $parser->functionIndex);
            //$function->compiled_cashe = base64_encode(serialize($interpreter));
            //$function->save();
            return $interpreter;
        //} else {
            //dd($fo);
            //return $fo;
        //}

    }

}