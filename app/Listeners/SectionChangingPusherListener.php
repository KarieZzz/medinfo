<?php

namespace App\Listeners;

use App\WorkerReadNotification;
use App\DocumentMessage;
use App\Events\DocumentSectionChanging;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SectionChangingPusherListener
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
     * @param  DocumentSectionChanging  $event
     * @return void
     */
    public function handle(DocumentSectionChanging $event)
    {
        //
        $worker = $event->documentSectionBlock->worker;
        $document = $event->documentSectionBlock->document;
        $formsection = $event->documentSectionBlock->formsection;
        $action = $event->documentSectionBlock->blocked ? 'принят' : 'отклонен';
        $newmessage = DocumentMessage::create(['doc_id' => $document->id, 'user_id' =>  $worker->id,
            'message' => "Раздел документа {$formsection->section_name} $action. "
        ]);
        $freshed = $newmessage->fresh();
        WorkerReadNotification::create(['worker_id' => $worker->id, 'event_uid' => $freshed->uid, 'event_type' => 3, 'occured_at' => $freshed->created_at ]);

        $options = array(
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'useTLS' => true
        );
        $auth_key = config('broadcasting.connections.pusher.key');
        $secret = config('broadcasting.connections.pusher.secret');
        $app_id = config('broadcasting.connections.pusher.app_id');
        $pusher = new \Pusher\Pusher(
            $auth_key,
            $secret,
            $app_id,
            $options
        );
        $url = config('app.url') . '/datainput/formdashboard/' . $document->id;
        $data['document_id'] = $document->id;
        $data['worker_id'] = $worker->id;
        $data['to_other'] = true;
        $data['message_header'] = <<<MESSAGE
<p class="text">Пользователем {$worker->description} изменен статус раздела отчетного документа 
<a href='$url' target='_blank'>(Id {$document->id}) по 
форме {$document->form->form_code}, МО: {$document->unit->unit_name}, отчетный период: {$document->period->name }</a></p>
MESSAGE;
        $data['message_body'] = $freshed->message;
        $pusher->trigger('event-brodcasting-channel', 'message-sent-event', $data);
    }
}
