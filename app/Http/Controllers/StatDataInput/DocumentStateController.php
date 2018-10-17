<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Worker;
use App\Unit;
use App\Medinfo\UnitTree;
use App\Document;
use App\DocumentMessage;
use App\Form;
use Mail;
use PhpParser\Node\Stmt\TryCatch;

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
        $data = [];
        $checker = new DataCheckController();
        $remark = $request->message;

        $old_state = Document::$state_aliases_keys[$request->oldstate];
        $new_state = Document::$state_aliases_keys[$request->state];

        $document = Document::find($request->document);
        if ($new_state == 4) {
            $protocol = $checker->check_document($document);
            if (!$protocol['valid']) {
                $data['status_changed'] = 0;
                $data['comment'] = "При контроле документа перед сменой статуса выявлены критические ошибки требующие исправлений. Смена статуса невозможна.";
                return $data;
            }
            if ($protocol['no_data'] && empty($remark)) {
                $data['status_changed'] = 0;
                $data['comment'] = "Документ не содержит данных. Нужно заполнить сообщение при смена статуса по какой причине он не заполнен.";
                return $data;
            }
        }
        $form = Form::find($document->form_id);
        $current_unit = Unit::find($document->ou_id);
        $worker = Auth::guard('datainput')->user();
        $miac_emails = explode(",", config('medinfo.miac_emails'));
        $director_emails = explode(",", config('medinfo.director_emails'));
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
        if ($p & config('medinfo.permission.permission_change_any_status')) {
            $document->save();
            $data['status_changed'] = 1;
        }
        else {
            switch ($new_state) {
                case 2 :
                    if($p & config('medinfo.permission.permission_set_status_accepted_declined')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } elseif ($old_state === 3) {
                        $document->save();
                        $data['status_changed'] = 1;
                    }
                    else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Выполняется\"";
                    }
                    break;
                case 3 :
                case 4 :
                    if($p & config('medinfo.permission.permission_set_status_prepared')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Подготовлен к проверке\"";
                    }
                    break;
                case 8 :
                case 16 :
                    if($p & config('medinfo.permission.permission_set_status_accepted_declined')) {
                        $document->save();
                        $data['status_changed'] = 1;
                    } else {
                        $data['status_changed'] = 0;
                        $data['comment'] = "Недостаточно прав для изменения статуса документа на \"Принят/Возвращен на доработку\"";
                    }
                    break;
                case 32 :
                    if($p & config('medinfo.permission.permission_set_status_approved')) {
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
                // TODO: Записать событие об изменении статуса в журнал
                //Log::storeFormStateChangeEvent($doc_id, $user->user_id, $old_state, $new_state);
                $newmessage = new DocumentMessage();
                $newmessage->doc_id = $document->id;
                $newmessage->user_id = $worker->id;
                $newlabel = Document::$state_labels[$document->state];
                $newalias = Document::$state_aliases[$document->state];
                $data['new_status'] = $newalias;
                $newmessage->message = "Статус документа изменен на \"". $newlabel . "\". " .  $remark;
                $newmessage->save();
                //dd(config('medinfo.permission'));
                $for_mail_body = compact('document', 'remark', 'worker','form', 'current_unit', 'newlabel');
                try {
                    Mail::send('emails.changestatemessage', $for_mail_body, function ($m) use ($emails) {
                        $m->from('medinfo@miac-io.ru', 'Email оповещение Мединфо');
                        $m->to($emails)->subject('Изменен статус отчетного документа Мединфо');
                    });
                    $data['sent_to'] = implode(",", $emails);
                } catch (\Exception $e) {
                    $data['sent_to'] = 'Почтовое сообщение о смене статуса документа не доставлено адресатам ' . implode(",", $emails);
                    $data['sent_error'] = $e->getMessage();
                }
            }
        }
        return $data;
    }

}
