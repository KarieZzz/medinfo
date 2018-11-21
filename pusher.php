<?php
/**
 * Created by PhpStorm.
 * User: shameev
 * Date: 20.11.2018
 * Time: 16:16
 */
require __DIR__ . '/vendor/autoload.php';

$options = array(
    'cluster' => 'eu',
    'useTLS' => true
);
$pusher = new Pusher\Pusher(
    'e595312f9b7d75b388bb',
    '2ce0b5fe700ec4cf28d7',
    '652822',
    $options
);

$data['message'] = 'hello world';
$pusher->trigger('event-brodcasting-channel', 'state-change-event', $data);