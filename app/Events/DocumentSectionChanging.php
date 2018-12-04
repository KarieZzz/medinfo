<?php

namespace App\Events;

use App\DocumentSectionBlock;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentSectionChanging extends Event
{
    use SerializesModels;
    public $documentSectionBlock;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DocumentSectionBlock $documentSectionBlock)
    {
        //
        $this->documentSectionBlock = $documentSectionBlock;
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
