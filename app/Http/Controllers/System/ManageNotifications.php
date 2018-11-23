<?php

namespace App\Http\Controllers\System;

use App\Worker;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ManageNotifications extends Controller
{
    //
    public function markMessagesAsRead()
    {
        $workers = Worker::all();
        \DB::delete("TRUNCATE worker_read_notifications");
        foreach ($workers as $worker) {
            $query = "INSERT INTO worker_read_notifications
              (worker_id, event_uid, event_type, occured_at) 
              select $worker->id, uid, 1, created_at from document_messages;";
            \DB::insert($query);
        }
    }
}
