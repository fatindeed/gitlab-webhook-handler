<?php

namespace Fatindeed\GitlabWebhookHandler\Tests;

use SplObserver;
use PHPUnit\Framework\TestCase;
use Fatindeed\GitlabWebhookHandler\EventSubject;
use Fatindeed\GitlabWebhookHandler\EventObserver;
use Fatindeed\GitlabWebhookHandler\DemoPushHook;
use Fatindeed\GitlabWebhookHandler\NullLogger;

final class EventSubjectTest extends TestCase
{
    public function testDetach()
    {
        $observer = new DemoPushHook;
        $subject = new EventSubject;
        $subject->attach($observer);
        $this->assertEquals(1, $subject->count());
        $subject->detach($observer);
        $this->assertEquals(0, $subject->count());
    }

    public function testObserverUpdateThrowException()
    {
        $observer = $this->getMockForAbstractClass(SplObserver::class);
        $observer->expects($this->once())
                 ->method('update')
                 ->will($this->throwException(new \Exception('phpunit test exception')));
        $logger = new NullLogger;
        $subject = new EventSubject($logger);
        $subject->attach($observer);
        $subject->notify();
        $this->assertStringContainsString('phpunit test exception', $logger->getContent());
    }
}
