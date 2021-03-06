<?php

namespace App\Http\Controllers\StatDataInput;

use App\Events\DocumentSendMessage;
use App\Medinfo\DocumentTreeByOU;
use App\WorkerProfile;
use App\WorkerReadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Worker;
use App\DocumentMessage;

class DocumentMessageController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function fetchMessages(Request $request)
    {
        $worker = Auth::guard('datainput')->user();
        $messages = DocumentMessage::OfDocument($request->document)->orderBy('created_at', 'desc')
            ->with('worker.profiles')
            ->with('is_read')
            ->withCount([
                'is_read' => function ($query) use ($worker) {
                    $query->where('worker_id', $worker->id);
                }
            ])
            ->get();
        return $messages;
    }

    public function fetchRecentMessages()
    {
        $worker = Worker::find(Auth::guard('datainput')->id());
        $tag = 'messageFeedLastRead';
        $ts = WorkerProfile::WorkerTag($worker->id, $tag)->first();
        if ($worker->worker_scopes[0]->ou_id === 0) {
            return [
                'ts' => is_null($ts) ? 0 : (float)$ts->value ,
                'messages' => DocumentMessage::orderBy('created_at','desc')
                    ->with('document.unit', 'document.form','worker.profiles','is_read')
                    ->withCount([
                        'is_read' => function ($query) use ($worker) {
                            $query->where('worker_id', $worker->id);
                        }
                    ])
                    ->take(50)
                    ->get()];
        } else {
            $documents = collect();
            foreach ($worker->worker_scopes as $scope) {
                //dd(DocumentTreeByOU::get($scope->ou_id));
                //$documents = array_merge($documents, DocumentTreeByOU::get($scope->ou_id) );
                $documents = $documents->merge(DocumentTreeByOU::get($scope->ou_id));
            }
            //dd($documents);
            //dd($worker);
            return [
                'ts' => is_null($ts) ? 0 : (float)$ts->value ,
                'messages' => DocumentMessage::orderBy('created_at','desc')
                    ->whereIn('doc_id', $documents)
                    ->with('document.unit', 'document.form','worker.profiles','is_read')
                    ->withCount([
                        'is_read' => function ($query) use ($worker) {
                            $query->where('worker_id', $worker->id);
                        }
                    ])
                    ->take(50)
                    ->get()];
        }
    }


    public function sendMessage(Request $request)
    {
        $this->validate($request, [
                'document' => 'required',
                'message' => 'required',
            ]
        );
        $doc_id = $request->document;
        //$document = Document::find($doc_id);
        $worker = Auth::guard('datainput')->user();
        $newmessage = new DocumentMessage();
        $newmessage->doc_id = $doc_id;
        $newmessage->user_id = $worker->id;
        $newmessage->message = $request->message;
        //try {
            $newmessage->save();
            $data = ['message_sent' => true];
            event(new DocumentSendMessage($newmessage));
        //} catch (\Exception $e) {
            //$data = ['message_sent' => false];
        //}
        return $data;
    }

    public function setLastReadTimestamp($timestamp)
    {
        $worker = Auth::guard('datainput')->user();
        $tag = \App\WorkerProfile::firstOrCreate(['worker_id' => $worker->id, 'tag' => 'messageFeedLastRead', 'attribute' => '']);
        $tag->value = $timestamp;
        $tag->save();
        return ['saved' => true];
    }

    public function markAllAsRead()
    {
        $worker = Worker::find(Auth::guard('datainput')->id());
        $tag = 'messageFeedLastRead';
        $ts = WorkerProfile::WorkerTag($worker->id, $tag)->first();
        $ts_value = is_null($ts) ? time() : (float)$ts->value;
        $latest_read =  \Carbon\Carbon::createFromTimestamp($ts_value);
        WorkerReadNotification::OfWorker($worker->id)->delete();
        if ($worker->worker_scopes[0]->ou_id === 0) {
            $latest_messages = DocumentMessage::where('created_at', '<', $latest_read)
                ->orderBy('created_at','desc')
                ->take(60)
                ->get();
            foreach ($latest_messages as $latest_message) {
                WorkerReadNotification::create(['worker_id' => $worker->id, 'event_uid' => $latest_message->uid, 'event_type' => 1, 'occured_at' => $latest_message->created_at ]);
            }
        } else {
            $documents = collect();
            foreach ($worker->worker_scopes as $scope) {
                $documents = $documents->merge(DocumentTreeByOU::get($scope->ou_id));
            }
            $latest_messages = DocumentMessage::where('created_at', '<', $latest_read)
                ->whereIn('doc_id', $documents)
                ->orderBy('created_at','desc')
                ->take(60)
                ->get();
            foreach ($latest_messages as $latest_message) {
                WorkerReadNotification::create(['worker_id' => $worker->id, 'event_uid' => $latest_message->uid, 'event_type' => 1, 'occured_at' => $latest_message->created_at ]);
            }
        }
        return ['result' => true];
    }
}
