<?php

namespace Fatindeed\GitlabWebhookHandler\Tests;

use PHPUnit\Framework\TestCase;
use Interop\Queue\Context;
use Interop\Queue\Producer;
use Interop\Queue\Consumer;
use Interop\Queue\Message;
use TimoReymann\GitlabWebhookLibrary\Event\EventType;
use Fatindeed\GitlabWebhookHandler\EventSubject;
use Fatindeed\GitlabWebhookHandler\DemoPushHook;
use Fatindeed\GitlabWebhookHandler\NullLogger;

final class AsyncHandlerTest extends TestCase
{
    protected $event;

    protected function setUp(): void
    {
        $this->event = (new EventType)->getEventDataFromBody(file_get_contents('tests/push-hook.json'));
    }

    public function testDispatchSuccess()
    {
        $producer = $this->createMock(Producer::class);
        $producer->expects($this->once())
                 ->method('send');
        $context = $this->createMock(Context::class);
        $context->expects($this->once())
                ->method('createProducer')
                ->willReturn($producer);
        $subject = new EventSubject;
        $subject->dispatch($this->event, $context);
    }

    public function testRunSuccess()
    {
        $message = $this->createMock(Message::class);
        $message->expects($this->once())
                ->method('getBody')
                ->willReturn(serialize($this->event));
        $consumer = $this->createMock(Consumer::class);
        $consumer->expects($this->once())
                 ->method('receive')
                 ->willReturn($message);
        $context = $this->createMock(Context::class);
        $context->expects($this->once())
                ->method('createConsumer')
                ->willReturn($consumer);
        $subject = new EventSubject;
        $subject->signalInstall(SIGTERM, [$subject, 'terminate']);
        posix_kill(posix_getpid(), SIGTERM);
        $subject->attach(new DemoPushHook);
        $this->expectOutputString('push');
        $subject->run($context);
    }

    public function testRunThrowException()
    {
        $consumer = $this->createMock(Consumer::class);
        $consumer->expects($this->once())
                 ->method('receive')
                 ->will($this->throwException(new \Exception('phpunit test exception')));
        $context = $this->createMock(Context::class);
        $context->expects($this->once())
                ->method('createConsumer')
                ->willReturn($consumer);
        $logger = new NullLogger;
        $subject = new EventSubject($logger);
        $subject->signalInstall(SIGTERM, [$subject, 'terminate']);
        posix_kill(posix_getpid(), SIGTERM);
        $subject->attach(new DemoPushHook);
        $subject->run($context);
        $this->assertStringContainsString('phpunit test exception', $logger->getContent());
    }
}
