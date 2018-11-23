<?php

namespace App\Events;

use App\DocumentMessage;
use App\Events\Event;
use App\Worker;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentSendMessage extends Event
{
    use SerializesModels;

    public $documentMessage;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DocumentMessage $documentMessage)
    {
        //
        $this->documentMessage = $documentMessage;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
