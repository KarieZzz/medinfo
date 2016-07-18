<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Worker;
use App\Unit;
use App\Form;
use App\Document;
use App\DocumentAudition;
use App\DocumentMessage;
use App\Medinfo\UnitTree;
use Mail;

class DocumentAuditionController extends Controller
{
    //
    public function fetchAuditions(Request $request)
    {
        $auditions = DocumentAudition::where('doc_id', $request->document)->with('dicauditstate', 'worker')->get();
        $noauditions['responce'] = 0;
        return count($auditions) > 0 ? $auditions :  $noauditions;
    }

    public function changeAudition(Request $request)
    {
        $this->validate($request, [
                'document' => 'required',
                'auditstate' => 'required',
            ]
        );
        $remark = $request->message;
        $new_audit_state = DocumentAudition::$audit_alias_keys[$request->auditstate];
        $document = Document::find($request->document);
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $worker = Auth::guard('datainput')->user();
        $miac_emails = explode(",", config('app.miac_emails'));
        $director_emails = explode(",", config('app.director_emails'));
        $parents = UnitTree::getParents($current_unit->id);
        $parents[] = $current_unit->id;
        $executors = Worker::getExecutorEmails($parents);
        $emails = array_merge($miac_emails, $director_emails, $executors);
        $p = $worker->permission;
        $data = array();
        if ($p & config('app.permission.permission_audit_document')) {
            $audition = $this->auditDocument($worker->id, $document->id, $new_audit_state);
            $data['audit_status_changed'] = 1;
            $newmessage = new DocumentMessage();
            $newmessage->doc_id = $document->id;
            $newmessage->user_id = $worker->id;
            $newlabel = DocumentAudition::$audit_labels[$audition->state_id];
            $newalias = DocumentAudition::$audit_aliases[$audition->state_id];
            $data['new_audit_status'] = $newalias;
            $newmessage->message = "Статус проверки документа изменен на \"". $newlabel . "\". " .  $remark;
            $newmessage->save();
            $for_mail_body = compact('document', 'remark', 'worker','form', 'current_unit', 'newlabel');
            Mail::send('emails.changeauditionmessage', $for_mail_body, function ($m) use ($emails) {
                $m->from('noreply@miac-io.ru', 'Email оповещение Мединфо');
                $m->to($emails)->subject('Проведена проверка отчетного документа Мединфо');
            });
            // TODO: Записать событие в журнал
        } else {
            $data['audit_status_changed'] = 0;
            $data['comment'] = "Нет прав для изменения статуса проверки документа";
        }
        return $data;
    }

    /**
     * Возможности удаления факта проверки отчетного документа не предусмотрено.
     * В случае отмены проверки - только замена статуса на "Не проверен".
     * @param int $worker
     * @param int $document
     * @param int $state
     * @return DocumentAudition
     */
    protected function auditDocument(int $worker, int $document, int $state)
    {
        $audition = DocumentAudition::ByWorkerAndDocument($worker, $document)->first();
        if ($audition) {
            $audition->state_id = $state;
            $audition->save();
        } else {
            $audition = new DocumentAudition();
            $audition->doc_id = $document;
            $audition->user_id = $worker;
            $audition->state_id = $state;
            $audition->save();
        }
        return $audition;
    }
}
