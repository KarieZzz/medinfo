<?php

namespace App\Listeners;

use App\Events\DocumentSendMessage;
use App\Worker;
use App\WorkerReadNotification;
use Mail;
use App\Medinfo\UnitTree;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentSendMessageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DocumentSendMessage  $event
     * @return void
     */
    public function handle(DocumentSendMessage $event)
    {
        //
        $worker = $event->documentMessage->worker;
        $document = $event->documentMessage->document;
        $doc_message = $event->documentMessage->fresh();
        WorkerReadNotification::create(['worker_id' => $worker->id, 'event_uid' => $doc_message->uid, 'event_type' => 1, 'occured_at' => $doc_message->created_at ]);
        $emails = array();
        if ($worker->email) {
            $emails[] = $worker->email;
        }
        // Исполнители данного отчета
        $parents = UnitTree::getParents($document->unit->id);
        $parents[] = $document->unit->id;
        $executors = Worker::getExecutorEmails($parents);
        // Эксперты МИАЦ
        $miac_emails = explode(",", config('medinfo.miac_emails') );
        // Руководители приема
        $director_emails = explode(",", config('medinfo.director_emails'));
        $emails = array_merge($emails, $miac_emails, $director_emails, $executors);
        $emails = array_unique($emails);
        $for_mail_body = compact('document', 'doc_message', 'worker','form', 'unit');
        Mail::send('emails.documentmessage', $for_mail_body, function ($m) use ($emails) {
            $m->from(config('medinfo.server_email'), 'Email оповещение Мединфо');
            $m->to($emails)->subject('Сообщение/комментарий к отчетному документу Мединфо');
        });
/*        $data['sent_to'] = implode(",", $emails);
        $error_emails = Mail::failures();
        if( count($error_emails) > 0 ) {
            foreach($error_emails as $email_address) {
                $data['error_emails'][] = $email_address;
            }
            $data['message_sent'] = false;
        } else {
            $data['message_sent'] = true;
        }*/
    }
}
