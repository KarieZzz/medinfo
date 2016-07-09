<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use Mail;
use App\Worker;
use App\Form;
use App\Unit;
use App\Document;
use App\WorkerScope;
use App\DocumentMessage;
use App\DocumentAudition;
use App\Medinfo\PeriodMM;
use App\Medinfo\UnitTree;
use App\Medinfo\DocumentTree;

class StatDataInput extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function index()
    {
        $worker_id = Auth::guard('datainput')->user()->id;
        $worker = Worker::find($worker_id);
        $worker_scope = WorkerScope::where('worker_id', $worker_id)->first()->ou_id;
        $permission = $worker->permission;
        $period = new PeriodMM(config('app.default_period'));
        $period_id = $period->getTableName();
        $disabled_states = config('app.disabled_states.' . $worker->role);
        if (!is_null($worker_scope)) {
            $mo_tree = UnitTree::getSimpleTree();
        }

        if ($permission & config('app.permission.permission_audit_document')) {
            $audit_permission = true;
        }
        else {
            $audit_permission = false;
        }
        return view('jqxdatainput.documentdashboard', compact('mo_tree', 'worker', 'worker_scope', 'period_id', 'disabled_states', 'audit_permission'));


    }

    public function fetchdocuments(Request $request)
    {
        $top_node = $request->ou;
        $states = explode(",", $request->states);
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'states', 'forms', 'periods');
        //$scopes_ = array('top_node' => $top_node, 'states' => $states, 'forms' => $forms, 'periods' => $periods );
        $d = new DocumentTree($scopes);
        $data = $d->get_documents();
        return $data;
    }

    public function fetchaggregates(Request $request)
    {
        $top_node = $request->ou;
        $states = array();
        $forms = explode(",", $request->forms);
        $periods = explode(",", $request->periods);
        $scopes = compact('top_node', 'states', 'forms', 'periods');
        $d = new DocumentTree($scopes);
        $data = $d->get_aggregates();
        return $data;
    }

    public function fetchmessages(Request $request)
    {
        $messages = DocumentMessage::where('doc_id', $request->document)->with('worker')->get();
        return $messages;
    }

    public function fetchauditions(Request $request)
    {
        $auditions = DocumentAudition::where('doc_id', $request->document)->with('dicauditstate', 'worker')->get();
        return $auditions;
    }

    public function sendMessage(Request $request)
    {
        $this->validate($request, [
                'document' => 'required',
                'message' => 'required',
            ]
        );
        $doc_id= $request->document;
        $remark = $request->message;
        $document = Document::find($doc_id);
        $form = Form::find($document->form_id);
        $unit = Unit::find($document->ou_id);
        $emails = array();
        $worker = Auth::guard('datainput')->user();
        if ($worker->email) {
            $emails[] = $worker->email;
        }
        // TODO: Добавить в список email всех исполнителей данного отчета
        // Эксперты МИАЦ
        $miac_emails = explode(",", config('app.miac_emails') );
        // Руководители приема
        $director_emails = explode(",", config('app.director_emails'));
        $emails = array_merge($emails, $miac_emails, $director_emails);
        // TODO: Решить что делать с отправкой email аудиторам
        $emails = array_unique($emails);
        $newmessage = new DocumentMessage();
        $newmessage->doc_id = $doc_id;
        $newmessage->user_id = $worker->id;
        $newmessage->message = $remark;
        $newmessage->save();
        $for_mail_body = compact('doc_id', 'remark', 'worker','form', 'unit');
        Mail::send('emails.documentmessage', $for_mail_body, function ($m) use ($emails) {
            $m->from('noreply@miac-io.ru', 'Email оповещение Мединфо');
            $m->to($emails)->subject('Сообщение/комментарий к отчетному документу Мединфо');
        });


        return $data['sent_to'] = implode(",", $emails);
    }

}
