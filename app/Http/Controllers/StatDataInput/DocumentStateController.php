<?php

namespace App\Http\Controllers\StatDataInput;

use App\Events\DocumentStateChanging;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Worker;
use App\Unit;
use App\Medinfo\UnitTree;
use App\Document;
//use App\Form;

class DocumentStateController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

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
        //$form = Form::find($document->form_id);
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
                $newalias = Document::$state_aliases[$document->state];
                $data['new_status'] = $newalias;
                event(new DocumentStateChanging(compact('worker', 'document','old_state','new_state', 'emails', 'remark')));
            }
        }
        return $data;
    }

}
