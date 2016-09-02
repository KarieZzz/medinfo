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

class DocumentMessageController extends Controller
{
    //
    public function fetchMessages(Request $request)
    {
        $messages = DocumentMessage::where('doc_id', $request->document)->orderBy('created_at', 'desc')->with('worker')->get();
        return $messages;
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
        // Исполнители данного отчета
        $parents = UnitTree::getParents($unit->id);
        $parents[] = $unit->id;
        $executors = Worker::getExecutorEmails($parents);
        // Эксперты МИАЦ
        $miac_emails = explode(",", config('app.miac_emails') );
        // Руководители приема
        $director_emails = explode(",", config('app.director_emails'));
        $emails = array_merge($emails, $miac_emails, $director_emails, $executors);
        // TODO: Решить что делать с отправкой email аудиторам
        $emails = array_unique($emails);
        $newmessage = new DocumentMessage();
        $newmessage->doc_id = $doc_id;
        $newmessage->user_id = $worker->id;
        $newmessage->message = $remark;
        $newmessage->save();
        $for_mail_body = compact('document', 'remark', 'worker','form', 'unit');
        Mail::send('emails.documentmessage', $for_mail_body, function ($m) use ($emails) {
            $m->from('noreply@miac-io.ru', 'Email оповещение Мединфо');
            $m->to($emails)->subject('Сообщение/комментарий к отчетному документу Мединфо');
        });
        $data['sent_to'] = implode(",", $emails);
        if( count(Mail::failures()) > 0 ) {
            foreach(Mail::failures as $email_address) {
                $data['error_emails'][] = $email_address;
            }
            $data['message_sent'] = false;
        } else {
            $data['message_sent'] = true;
        }
        return $data;
    }
}
