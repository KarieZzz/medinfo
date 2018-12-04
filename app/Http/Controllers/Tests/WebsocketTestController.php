<?php

namespace App\Http\Controllers\Tests;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class WebsocketTestController extends Controller
{
    //
    public function websocket()
    {
        $options = array(
            'cluster' => 'eu',
            'useTLS' => true
        );
        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            $options
        );
        $channel = 'event-brodcasting-channel';
        $event = 'state-change-event';
        $data['message'] = "<p class='text-warning'><strong>Изменен статус отчетного документа (Id fake)</strong></p>";
        $data['worker'] =  "<p class='text-info small'>Исполнитель: fake</p>";
        $data['form'] =  "<p class='text-info small'>Отчетная форма: (fake) fake</p>";
        $data['unit'] =  "<p class='text-info small'>Медицинская организация: fake</p>";
        $data['period'] =  "<p class='text-info small'>Отчетный период: fake</p>";
        $pusher->trigger($channel, $event, $data);
        return ['message' => "В канал $channel отправлено событие $event"];
    }
}
