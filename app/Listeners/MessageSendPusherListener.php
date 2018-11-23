<?php

namespace App\Listeners;

use App\Events\DocumentSendMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageSendPusherListener
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
<p class="text">Пользователем {$worker->description} оставлено сообщение для отчетного документа 
<a href="$url" target="_blank">(Id {$document->id}) по 
форме {$document->form->form_name}, МО: {$document->unit->unit_name}, отчетный период: {$document->period->name }</a></p>
MESSAGE;
        $data['message_body'] = $doc_message->message;
        $pusher->trigger('event-brodcasting-channel', 'message-sent-event', $data);
    }
}
