<?php

namespace App\Http\Controllers\StatDataInput;

use App\Events\DocumentSendMessage;
use App\WorkerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Worker;
use App\Unit;
//use App\Medinfo\UnitTree;
use App\Document;
use App\DocumentMessage;
use App\Form;
use Mail;

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
        $messages = DocumentMessage::where('doc_id', $request->document)->orderBy('created_at', 'desc')
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
        $worker = Auth::guard('datainput')->user();
        $tag = 'messageFeedLastRead';
        $ts = WorkerProfile::WorkerTag($worker->id, $tag)->first();
        return [
            'ts' => is_null($ts) ? 0 : (float)$ts->value ,
            'messages' => \App\DocumentMessage::orderBy('created_at','desc')
            ->with('document.unit', 'document.form','worker.profiles','is_read')->take(50)->get()];
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
}
