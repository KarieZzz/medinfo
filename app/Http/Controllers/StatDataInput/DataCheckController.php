<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Form;
use App\Table;
use App\Document;
use App\Period;
use App\CFunction;
use App\Medinfo\Control\DataCheck;
use App\Medinfo\DocumentTree;
use App\Medinfo\Lexer\FunctionDispatcher;
use App\Medinfo\Control\ControlHelper;

class DataCheckController extends Controller
{
    //


    public function informTableControl(Document $document, Table $table, int $forcereload = 0)
    {
        return DataCheck::inFormtableControl($document, $table, $forcereload);
    }

    public function interFormControl(Document $document, Table $table, int $forcereload = 0)
    {
        return DataCheck::interFormTableControl($document, $table, $forcereload);
    }

    public function interPeriodControl(Document $document, Table $table, int $forcereload = 0)
    {
        return DataCheck::interPeriodTableControl($document, $table, $forcereload);
    }

    public function check_table(Document $document, Table $table, int $forcereload = 0)
    {
        return DataCheck::tableControl($document, $table, $forcereload);
    }

    public function check_document(Document $document, int $forcereload = 0)
    {
        $form_protocol = [];
        $form_protocol['valid'] = true;
        $form_protocol['no_alerts'] = true;
        ControlHelper::formContainsData($document->id) ? $form_protocol['no_data'] = false : $form_protocol['no_data'] = true;
        if ($forcereload) {
            $form_protocol['forcereloaded'] = true;
        }
        $form_id = $document->form_id;
        $album_id = $document->album_id;
        $tables = Table::OfForm($form_id)->whereDoesntHave('excluded', function ($query) use($album_id) {
            $query->where('album_id', $album_id);
        })->orderBy('table_index')->get();
        foreach ($tables as $table) {
            $offset = $table->table_code;
            $control = DataCheck::tableControl($document, $table, null, $forcereload, true);
            if (!$control['valid'] || !$control['no_alerts']) {
                //dd($control);
                $form_protocol[$offset] = $control;
                $form_protocol['valid'] = $form_protocol['valid'] && $control['valid'];
                $form_protocol['no_alerts'] = $form_protocol['no_alerts'] && $control['no_alerts'];
                //$form_protocol['no_data'] = false;
            }
        }
        return $form_protocol;
    }

    public function selectControlConditions()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        return view('jqxadmin.selectedcontrolconditions', compact('forms'));
    }

    public function selectedControl(Request $request)
    {
        $this->validate($request, [
                'form' => 'required|integer',
                'cfunctions' => 'required',
                //'mode' => 'required|in:1,2',
                //'level' => 'integer',
            ]
        );
        set_time_limit(1800);
        $form = Form::find($request->form);
        $cfunctions = explode(',', $request->cfunctions);
        $period = Period::orderBy('begin_date', 'desc')->first();
        $worker_scope = 0;
        $filter_mode = 1;
        $top_node = '0';
        $dtypes = [ 1 ];
        $states = [];
        $forms = [ $form->id ];
        $periods = [ $period->id ];
        $scopes = compact('worker_scope', 'filter_mode', 'top_node', 'dtypes', 'states', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $documents = $d->get_documents();
        $interpreters = [];
        $selected_protocol = [];
        $output = [];
        foreach ($cfunctions as $cfunction) {
            $cf = CFunction::find($cfunction);
            $t = Table::find($cf->table_id);
            $interpreters[$cfunction]['i'] = DataCheck::cacheOrCompile($cf, $t);
            $interpreters[$cfunction]['f'] = $cf;

            //$interpreters[$cfunction] = $fromDatabase = unserialize(base64_decode($cf->compiled_cashe));
        }
        foreach ($documents as $document) {
            $document_protocol = [];
            $valid = true;
            //dd($cfunctions);
            foreach ($cfunctions as $cfunction) {
                // TODO: Работает корректно, только если сделать копию объекта
                $interpreter = unserialize(serialize($interpreters[$cfunction]['i']));
                //$interpreter = $interpreters[$cfunction]['i'];
                //$interpreter->setArguments();
                $cf = $interpreters[$cfunction]['f'];
                //dd(Document::find($document->id));

                //$cf = CFunction::find($cfunction);
                //$t = Table::find($cf->table_id);
                //$interpreter = DataCheck::cacheOrCompile($cf, $t);

                $rule = $interpreter->exec(Document::find($document->id));
                //dd($interpreter);
                $rule['function_id'] = $interpreter->functionIndex;
                $rule['function'] = FunctionDispatcher::$structNames[$interpreter->functionIndex];
                $rule['level'] = $cf->level;
                $rule['input'] = $cf->script;
                $rule['comment'] = $cf->comment;
                if ($rule['not_in_scope']) {
                    $rule['comment'] .= " Правило контроля не применяется к данному документу (ограничения по группе медицинских организаций)";
                }
                if (isset($rule['errors'])) {
                    foreach($rule['errors'] as $error) {
                        $selected_protocol['errors'][] =  $error;
                    }
                }
                $rule['no_rules'] = false;
                $document_protocol[$cfunction] = $rule;
                // При проверке валидности данных по таблице учитываем только скрипты уровня "ошибка"
                $valid = $valid && $rule['valid'];
            }
            //$document_protocol['valid'] = $valid;
            //dd($document_protocol);
            //dd($document);
            $selected_protocol[$document->id]['protocol'] = $document_protocol;
            $selected_protocol[$document->id]['document'] = $document->id;
            $selected_protocol[$document->id]['valid'] = $valid;
            $selected_protocol[$document->id]['unit_name'] = $document->unit_name;
            $selected_protocol[$document->id]['unit_code'] = $document->unit_code;
            //$output = collect($selected_protocol);
            //$output->sortBy('unit_code');
           //output = $selected_protocol;
        }
        //dd($selected_protocol);
        return view('reports.selectedcontroloutput', compact('selected_protocol', 'form', 'period'));

    }

}
