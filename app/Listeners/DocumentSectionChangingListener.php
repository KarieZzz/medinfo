<?php

namespace App\Listeners;

use App\Worker;
use Carbon\Carbon;
use Mail;
use App\SectionchangingLog;
use App\Events\DocumentSectionChanging;
use App\Medinfo\UnitTree;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentSectionChangingListener
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
     * @param  DocumentSectionChanging  $event
     * @return void
     */
    public function handle(DocumentSectionChanging $event)
    {
        //
        $worker = $event->documentSectionBlock->worker;
        $document = $event->documentSectionBlock->document;
        $formsection = $event->documentSectionBlock->formsection;

        SectionchangingLog::create(['worker_id' => $worker->id, 'document_id' => $document->id, 'formsection_id' => $formsection->id,
            'blocked' =>  $event->documentSectionBlock->blocked, 'occured_at' => Carbon::now()]);
        $action = $event->documentSectionBlock->blocked ? 'принят' : 'отклонен';
       // Исполнители данного отчета
        $emails = [];
        if ($worker->email) {
            $emails[] = $worker->email;
        }
        $parents = UnitTree::getParents($document->unit->id);
        $parents[] = $document->unit->id;
        $executors = Worker::getExecutorEmails($parents);
        // Эксперты МИАЦ
        $miac_emails = explode(",", config('medinfo.miac_emails') );
        // Руководители приема
        $director_emails = explode(",", config('medinfo.director_emails'));
        $emails = array_merge($emails, $miac_emails, $director_emails, $executors);
        $emails = array_unique($emails);
        $for_mail_body = compact('document', 'formsection', 'worker', 'action');
        try {
            Mail::send('emails.changesectionmessage', $for_mail_body, function ($m) use ($emails) {
                $m->from(config('medinfo.server_email'), 'Email оповещение Мединфо');
                $m->to($emails)->subject('Принят/отклонен раздел отчетного документа Мединфо');
            });
            $data['sent_to'] = implode(",", $emails);
        } catch (\Exception $e) {
            $data['sent_to'] = 'Почтовое сообщение о приеме/отклонении раздела документа не доставлено адресатам ' . implode(",", $emails);
            $data['sent_error'] = $e->getMessage();

        }
    }
}
