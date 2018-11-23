<?php

namespace App\Http\Controllers\StatDataInput;

use App\Events\DocumentSendMessage;
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

}
