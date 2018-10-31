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
//use App\Medinfo\ControlHelper;
//use App\Medinfo\DSL\ControlFunctionEvaluator;
//use App\Medinfo\DSL\ControlFunctionParser;


//use App\Medinfo\Lexer\ControlFunctionLexer;
//use App\Medinfo\Lexer\ControlFunctionParser;
//use App\Medinfo\Lexer\CompareControlInterpreter;
//use App\Medinfo\Lexer\FunctionDispatcher;


class DataCheck
{
    /*
      public static function tableControl(Document $document, Table $table, $forcereload = 0)
       {
           set_time_limit(240);
           if (ControlHelper::CashedProtocolActual($document->id, $table->id) && !$forcereload) {
               $table_protocol = ControlHelper::loadProtocol($document->id, $table->id);
               return $table_protocol;
           }
           if (ControlHelper::tableContainsData($document->id, $table->id)) {
               $table_protocol['no_data'] = false;
           } else {
               $table_protocol['no_data'] = true;
           }
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
       }*/

    public static function inFormtableControl(Document $document, Table $table, $forcereload = 0)
    {
        $cfunctions = CFunction::OfTable($table->id)->InForm()->Active()->get();
        $protocol = self::tableControl($document, $table, $cfunctions, $forcereload = 0);
        return $protocol;
    }

    public static function interFormTableControl(Document $document, Table $table, $forcereload = 0)
    {
        $cfunctions = CFunction::OfTable($table->id)->InterForm()->Active()->get();
        $protocol = self::tableControl($document, $table, $cfunctions, $forcereload = 0);
        return $protocol;
    }

    public static function interPeriodTableControl(Document $document, Table $table, $forcereload = 0)
    {
        $cfunctions = CFunction::OfTable($table->id)->InterPeriod()->Active()->get();
        $protocol = self::tableControl($document, $table, $cfunctions, $forcereload = 0);
        return $protocol;
    }

    public static function tableControl(Document $document, Table $table, $cfunctions = null, $forcereload = 0, $only_errors = false)
    {

        set_time_limit(240);
        if (!$cfunctions) {
            $cfunctions = CFunction::OfTable($table->id)->Active()->get();
        }
        $table_protocol = [];
        //if (ControlHelper::CashedProtocolActual($document->id, $table->id) && !$forcereload) {
        if (ControlHelper::CashedProtocolActual($document->id, $table->id) && config('medinfo.use_cashed_protocol')) {
            $table_protocol = ControlHelper::loadProtocol($document->id, $table->id);
            return $table_protocol;
        }
        if (ControlHelper::tableContainsData($document->id, $table->id)) {
            $table_protocol['no_data'] = false;
        } else {
            $table_protocol['no_data'] = true;
        }
        $table_protocol['table_id'] = $table->id;

        //dd('control');
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
                $pTree = unserialize(base64_decode($function->ptree));
                $props = json_decode($function->properties, true);
                //$evaluator = new ControlFunctionEvaluator($pTree, $props, $document);
                $evaluator = \App\Medinfo\DSL\Evaluator::invoke($pTree, $props, $document);
                $rule['iterations'] = $evaluator->makeControl();
                $rule['valid'] = $evaluator->valid;
                $rule['boolean_sign'] = isset($props['boolean_sign']) ? $props['boolean_sign'] : null;
                $rule['formula'] = $props['formula'];
                $rule['function_id'] = $props['function_id'];
                $rule['function'] = $props['function'];
                $rule['iteration_mode'] = $props['iteration_mode'];
                $rule['level'] = $function->level;
                $rule['input'] = $function->script;
                $rule['comment'] = $function->comment;
                if ($evaluator->not_in_scope) {
                    $rule['not_in_scope'] = true;
                    $rule['comment'] .= " " . implode(' ', $evaluator->comment) ;
                } else {
                    $rule['not_in_scope'] = false;
                }
/*                if (isset($rule['errors'])) {
                    foreach($rule['errors'] as $error) {
                        $table_protocol['errors'][] =  $error;
                    }
                }*/
                $rule['no_rules'] = false;
                // При проверке валидности данных по таблице учитываем только скрипты уровня "ошибка"
                if ($function->level == 1) {
                    //dd($rule);
                    $valid = $valid && $rule['valid'];
                } elseif ($function->level == 2) {
                    $do_not_alerted = $do_not_alerted && $rule['valid'];
                }
                if (!$only_errors ) {
                    $rules[] = $rule;
                } elseif ($only_errors && !$rule['valid']) {
                    $rules[] = $rule;
                }
            }
            catch (\Exception $e) {
                $rules[] = ['error' => "<strong class='text-danger'>Ошибка при обработке правила контроля:</strong> <code>" . $function->script . '</code> ' . $e->getMessage() ];
            }
        }
        $table_protocol['valid'] = $valid;
        $table_protocol['no_alerts'] = $do_not_alerted;
        if (config('medinfo.cash_control_protocol')) {
            ControlHelper::cashProtocol($table_protocol, $document->id, $table->id);
        }
        return $table_protocol;
    }

}