<?php

namespace Fatindeed\GitlabWebhookHandler\Tests;

use PHPUnit\Framework\TestCase;
use TimoReymann\GitlabWebhookLibrary\Event\EventType;
use Fatindeed\GitlabWebhookHandler\EventSubject;
use Fatindeed\GitlabWebhookHandler\DemoPushHook;

final class SyncHandlerTest extends TestCase
{
    protected $event;

    protected function setUp(): void
    {
        $this->event = (new EventType)->getEventDataFromBody(file_get_contents('tests/push-hook.json'));
    }

    public function testDispatchSuccess()
    {
        $subject = new EventSubject;
        $subject->attach(new DemoPushHook);
        $this->expectOutputString('push');
        $subject->dispatch($this->event);
    }
}
