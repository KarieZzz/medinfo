<?php

namespace App\Listeners;

use App\Events\DocumentStateChanging;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationPusherListener
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
     * @param  DocumentStateChanging  $event
     * @return void
     */
    public function handle(DocumentStateChanging $event)
    {
        //
        $worker = $event->happening['worker'];
        $document = $event->happening['document'];
        $old_state = $event->happening['old_state'];
        $new_state = $event->happening['new_state'];
        $remark = $event->happening['remark'];
        $emails = $event->happening['emails'];
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

        $data['document_id'] = $document->id;
        $data['worker_id'] = $worker->id;
        $data['to_other'] = true;

        $data['message'] = "<p class='text-warning'><strong>Изменен статус отчетного документа (Id {$document->id})</strong></p>";
        $data['worker'] =  "<p class='text-info small'>Исполнитель: {$worker->description}</p>";
        $data['form'] =  "<p class='text-info small'>Отчетная форма: ({$document->form->form_code}) {$document->form->form_name}</p>";
        $data['unit'] =  "<p class='text-info small'>Медицинская организация: {$document->unit->unit_name}</p>";
        $data['period'] =  "<p class='text-info small'>Отчетный период: {$document->period->name}</p>";
        $pusher->trigger('event-brodcasting-channel', 'state-change-event', $data);
    }
}
