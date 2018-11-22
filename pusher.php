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

$data['message'] = "<p class='text-warning'><strong>Изменен статус отчетного документа (Id fake)</strong></p>";
$data['worker'] =  "<p class='text-info small'>Исполнитель: fake</p>";
$data['form'] =  "<p class='text-info small'>Отчетная форма: (fake) fake</p>";
$data['unit'] =  "<p class='text-info small'>Медицинская организация: fake</p>";
$data['period'] =  "<p class='text-info small'>Отчетный период: fake</p>";
$pusher->trigger('event-brodcasting-channel', 'state-change-event', $data);