<?php

namespace App\Http\Controllers\Admin;

//use App\Medinfo\PeriodMM;
use App\Unit;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Medinfo\DocumentTree;
use App\Period;
use App\Document;
use App\DicDocumentState;
use App\DicDocumentType;
use App\DocumentMessage;
use App\Form;
use App\Cell;

class DocumentAdminController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $forms = Form::orderBy('form_index')->get(['id', 'form_code']);
        $states = DicDocumentState::all(['code', 'name']);
        $dtypes = DicDocumentType::all(['code', 'name']);
        $periods = Period::orderBy('begin_date', 'desc')->get(['id', 'name']);
        $form_ids = $forms->pluck('id');
        $state_ids = $states->pluck('code');
        $period_ids = $periods[0]->id;
        $dtype_ids = $dtypes->pluck('code');
        //dd($periods);
        //return $periods;
        return view('jqxadmin.documents', compact('forms', 'form_ids', 'states', 'state_ids', 'periods', 'period_ids', 'dtypes', 'dtype_ids'));
    }

    public function fetchDocuments(Request $request)
    {
        $top_node = $request->ou;
        $dtypes = explode(",", $request->dtypes);
        $states = explode(",", $request->states);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'dtypes', 'states', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_documents();
        return $data;
    }

    public function createDocuments(Request $request)
    {
        $units = explode(",", $request->units);
        $forms = explode(",", $request->forms);
        $create_primary = $request->primary;
        $create_aggregate = $request->aggregate;
        $period_id = $request->period;
        $initial_state = $request->state;
        $i = 0;
        $duplicate = 0;
        foreach ($units as $unit_id) {
            $unit = Unit::find($unit_id);
            foreach ($forms as $form_id) {
                $newdoc = ['ou_id' => $unit_id, 'form_id' => $form_id , 'period_id' => $period_id, 'state' => $initial_state ];
                if ($create_primary && $unit->report) {
                    $newdoc['dtype'] = 1;
                    try {
                        Document::create($newdoc);
                        $i++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errorCode = $e->errorInfo[1];
                        // duplicate key value - код ошибки 7 при использовании PostgreSQL
                        if($errorCode == 7){
                            $duplicate++;
                        }
                    }
                }
                if ($create_aggregate && $unit->aggregate) {
                    $newdoc['dtype'] = 2;
                    try {
                        Document::create($newdoc);
                        $i++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errorCode = $e->errorInfo[1];
                        if($errorCode == 7){
                            $duplicate++;
                        }
                    }
                }
            }
        }
        $data['count_of_created'] = $i;
        $data['count_of_duplicated'] = $duplicate;
        $data['count_of_all'] = count($units)*count($forms);
        return $data;
    }

    public function deleteDocuments(Request $request)
    {
        $documents = explode(",", $request->documents );
        // Удаление статданных из всех выбранных документов
        Cell::WhereIn('doc_id', $documents)->delete();
        $comment = 'Удалены статданные документов №№' . $request->documents . ' ';
        $affected = Document::destroy($documents);
        $comment .= 'Удалены документы №№' . $request->documents;
        $data['comment'] = $comment;
        $data['documents_deleted'] = 1;
        $data['affected_documents'] = $affected;
        return $data;
    }

    public function eraseStatData(Request $request)
    {
        $documents = explode(",", $request->documents );
        $affected = Cell::WhereIn('doc_id', $documents)->delete();
        $comment = 'Удалены статданные из документов №№ ' . $request->documents;
        $data['comment'] = $comment;
        $data['statdata_erased'] = 1;
        $data['affected_cells'] = $affected;
        return $data;
    }

    public function changeState(Request $request)
    {
        $documents = explode(",", $request->documents );
        $affected = Document::WhereIn('id', $documents)->update(['state' => $request->state]);
        $data['comment'] = "Изменен статус документов.";
        $data['state_changed'] = 1;
        $data['affected_documents'] = $affected;
        // Сообщения пока записываем от имени Администратора - TODO: Записывать сообщения от реального пользователя
        $this->bulkDocumentMessage($documents, 1, 'Изменен статус документа.');
        return $data;
    }

    public function bulkDocumentMessage(array $documents, int $worker, $message)
    {
        foreach ($documents as $d) {
            DocumentMessage::create(['doc_id' => $d, 'user_id' => $worker, 'message' => $message]);
        }
    }
}
