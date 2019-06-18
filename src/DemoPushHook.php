<?php

namespace Fatindeed\GitlabWebhookHandler;

use TimoReymann\GitlabWebhookLibrary\Specification\Event;
use TimoReymann\GitlabWebhookLibrary\Event\PushEvent;

class DemoPushHook extends EventObserver
{
    /**
     * @var string
     */
    protected $eventTypes = [PushEvent::class];

    /**
     * Process push event
     * 
     * @return void
     */
    protected function process(): void
    {
        echo $this->event->getObjectKind();
    }
}