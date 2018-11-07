<?php

namespace App\Http\Controllers\StatDataInput;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Document;
use App\DocumentSectionBlock;
use App\FormSection;
use App\DocumentMessage;
use App\SectionchangingLog;
use Carbon\Carbon;

class DocumentSectionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('datainputauth');
    }

    public function toggleSection(Document $document, FormSection $formsection, $blocking = '1')
    {
        $worker = Auth::guard('datainput')->user();
        $blocked = $blocking === '1' ? true : false;
        $section = DocumentSectionBlock::SD($formsection->id, $document->id)->first();
        if (!$section) {
            $section = DocumentSectionBlock::create(
                [
                    'formsection_id' => $formsection->id ,
                    'document_id' => $document->id,
                    'worker_id' => $worker->id,
                    'blocked' =>$blocked
                ]
            );
        } else {
            $section->worker_id = $worker->id;
            $section->blocked = $blocked;
            $section->save();
        }

        SectionchangingLog::create(['worker_id' => $worker->id, 'document_id' => $document->id, 'formsection_id' => $formsection->id,
            'blocked' => $section->blocked, 'occured_at' => Carbon::now()]);
        $action = $section->blocked ? 'принят' : 'отклонен';
        $newmessage = new DocumentMessage();
        $newmessage->doc_id = $document->id;
        $newmessage->user_id = $worker->id;
        $newmessage->message = "Раздел документа {$formsection->section_name} $action. ";
        $newmessage->save();

        return ['section' => $section, 'worker' => $section->worker ] ;
    }

}
