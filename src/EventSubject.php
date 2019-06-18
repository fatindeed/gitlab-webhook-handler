<?php

namespace Fatindeed\GitlabWebhookHandler;

use Exception, SplSubject, SplObserver, SplObjectStorage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Interop\Queue\Context;
use TimoReymann\GitlabWebhookLibrary\Specification\Event;

/**
 * @see https://www.php.net/manual/zh/class.splsubject.php
 */
class EventSubject implements SplSubject
{
    use LoggerAwareTrait, SignalHandler;

    /**
     * @var \SplObjectStorage
     */
    private $observers;

    /**
     * @var \TimoReymann\GitlabWebhookLibrary\Specification\Event
     */
    private $event;

    /**
     * @var bool
     */
    private $loop = true;

    const DEFAULT_QUEUE_NAME = 'gitlab-webhook-queue';

    /**
     * @param  \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->observers = new SplObjectStorage;
        $this->setLogger($logger ?? new NullLogger);
    }

    /**
     * Attach an SplObserver
     * 
     * @param  \SplObserver $observer
     * @return void
     */
    public function attach(SplObserver $observer): void
    {
        $this->observers->attach($observer);
    }

    /**
     * Detach an observer
     * 
     * @param  \SplObserver $observer
     * @return void
     */
    public function detach(SplObserver $observer): void
    {
        $this->observers->detach($observer);
    }

    /**
     * Returns the number of observers in the storage
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->observers->count();
    }

    /**
     * Notify an observer
     * 
     * @return void
     */
    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            try {
                $observer->update($this);
            } catch (Exception $e) {
                $this->logger->error(get_class($observer).': '.$e->getMessage());
            }
        }
    }

    /**
     * Get event
     * 
     * @return \TimoReymann\GitlabWebhookLibrary\Specification\Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * Set event
     * 
     * @param  \TimoReymann\GitlabWebhookLibrary\Specification\Event $event
     * @return void
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * Dispatch event
     * 
     * @param  \TimoReymann\GitlabWebhookLibrary\Specification\Event $event
     * @param  \Interop\Queue\Context|null $context
     * @return void
     */
    public function dispatch(Event $event, ?Context $context = null): void
    {
        if ($context) {
            $queue = $context->createQueue($_ENV['QUEUE_NAME'] ?? self::DEFAULT_QUEUE_NAME);
            $message = $context->createMessage(serialize($event));
            $context->createProducer()->send($queue, $message);
        } else {
            $this->setEvent($event);
            $this->notify();
        }
    }

    /**
     * Run infinite loop
     * 
     * @param  \Interop\Queue\Context $context
     * @return void
     */
    public function run(Context $context): void
    {
        $queue = $context->createQueue($_ENV['QUEUE_NAME'] ?? self::DEFAULT_QUEUE_NAME);
        $consumer = $context->createConsumer($queue);
        while ($this->loop) {
            try {
                $message = $consumer->receive(5000);
                if ($message) {
                    $event = unserialize($message->getBody());
                    $this->dispatch($event);
                    $consumer->acknowledge($message);
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $this->signalDispatch();
        }
    }

    /**
     * SIGTERM signal handler
     * 
     * @param  int $signo
     * @return void
     */
    public function terminate(int $signo): void
    {
        $this->logger->info('Gitlab webhook handler terminated on signal #'.$signo.'.');
        $this->loop = false;
    }
}