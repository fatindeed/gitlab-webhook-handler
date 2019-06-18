<?php

namespace Fatindeed\GitlabWebhookHandler;

use SplSubject, SplObserver;
use TimoReymann\GitlabWebhookLibrary\Specification\Event;

/**
 * @see https://www.php.net/manual/zh/class.splobserver.php
 */
abstract class EventObserver implements SplObserver
{
    /**
     * Event type to be handled
     * 
     * @var array
     */
    protected $eventTypes = [];

    /**
     * @var \TimoReymann\GitlabWebhookLibrary\Specification\Event
     */
    protected $event;

    /**
     * Receive update from subject
     * 
     * @param  \SplSubject $subject
     * @return void
     */
    public function update(SplSubject $subject): void
    {
        if ($subject instanceof EventSubject) {
            $this->event = $subject->getEvent();
            if ($this->shouldProcess()) {
                $this->process();
            }
        }
    }

    /**
     * Should process webhook event
     *
     * @return bool
     */
    protected function shouldProcess(): bool
    {
        return in_array(get_class($this->event), $this->eventTypes);
    }

    /**
     * Process webhook event
     * 
     * @return void
     */
    abstract protected function process(): void;
}