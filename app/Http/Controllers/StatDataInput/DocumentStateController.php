<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Worker;
use App\Unit;
use App\Medinfo\UnitTree;
use App\Document;
use App\DocumentMessage;
use App\Form;
use Mail;

class DocumentStateController extends Controller
{
    //
    public function changeState(Request $request)
    {
        $this->validate($request, [
                'document' => 'required',
                'state' => 'required',
            ]
        );
        $remark = $request->message;
        $new_state = Document::$state_aliases_keys[$request->state];
        $document = Document::find($request->document);
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $worker = Auth::guard('datainput')->user();
        $emails = array();
        $miac_emails = explode(",", config('app.miac_emails'));
        $director_emails = explode(",", config('app.director_emails'));
        $old_state = $document->state;
        $document->state = $new_state;
        $parents = UnitTree::getParents($current_unit->id);
        $parents[] = $current_unit->id;
        //$all_units = Unit::find($parents);
        //$all_units->load(['workerScope.workers' => function($query) {
            //$query->where('role', 1);
        //}]);
        $executors = Worker::getExecutorEmails($parents);
        // TODO: Отправлять или нет сообщения аудиторам?
        $emails = array_merge($miac_emails, $director_emails, $executors);
        $p = $worker->permission;
        $data = array();
        if ($p & config('app.permission.permission_change_any_status')) {
            $document->save();
            $data['status_changed'] = 1;
        }
        else {
            switch ($new_state) {
                case 2 :
                    if($p & config('app.permission.permission_set_status_accepted_declined')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Выполняется\"";
                    }
                    break;
                case 4 :
                    if($p & config('app.permission.permission_set_status_prepared')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Подготовлен к проверке\"";
                    }
                    break;
                case 8 :
                case 16 :
                    if($p & config('app.permission.permission_set_status_accepted_declined')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Принят/Возвращен на доработку\"";
                    }
                    break;
                case 32 :
                    if($p & config('app.permission.permission_set_status_approved')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Утвержден\"";
                    }
                    break;
                default :
                    $data['status_changed'] = 0;
                    $data['comment'] = 'Неизвестный статус документа';
                    break;
            }
            if ($data['status_changed']) {
                // TODO: Записать событие в журнал
                //Log::storeFormStateChangeEvent($doc_id, $user->user_id, $old_state, $new_state);
                $newmessage = new DocumentMessage();
                $newmessage->doc_id = $document->id;
                $newmessage->user_id = $worker->id;
                $newlabel = Document::$state_labels[$document->state];
                $newalias = Document::$state_aliases[$document->state];
                $data['new_status'] = $newalias;
                $newmessage->message = "Статус документа изменен на \"". $newlabel . "\". " .  $remark;
                $newmessage->save();
                $for_mail_body = compact('document', 'remark', 'worker','form', 'current_unit', 'newlabel');
                Mail::send('emails.changestatemessage', $for_mail_body, function ($m) use ($emails) {
                    $m->from('noreply@miac-io.ru', 'Email оповещение Мединфо');
                    $m->to($emails)->subject('Изменен статус отчетного документа Мединфо');
                });
            }
        }
        return $data;
    }

}
