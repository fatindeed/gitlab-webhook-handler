# GitLab Webhook handler

[![PHP Version][ico-php-v]](#)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](#)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-packagist]

<!-- This library is a _pure PHP_ implementation of the [AMQP 0-9-1 protocol](http://www.rabbitmq.com/tutorials/amqp-concepts.html).
It's been tested against [RabbitMQ](http://www.rabbitmq.com/). -->

## Install

```sh
composer require fatindeed/gitlab-webhook-handler
```

## Developer Guide

*TODO*

## User Guide

### Create Event

1.  With Secret token

    ```php
    use TimoReymann\GitlabWebhookLibrary\Core\Webhook;
    use TimoReymann\GitlabWebhookLibrary\Token\SecretToken;

    $hook = new Webhook(new SecretToken('mySuperSecretToken'));
    $event = $hook->parse();
    ```

2.  Without Secret token

    ```php
    use TimoReymann\GitlabWebhookLibrary\Event\EventType;

    $eventType = new EventType;
    $event = $eventType->getEventDataFromBody(file_get_contents('php://input'));
    ```

### Sync Handler Example

```php
use Fatindeed\GitlabWebhookHandler\EventSubject;

$object = new YourWebhookHandler; // Replace with you webhook handler

$subject = new EventSubject;
$subject->attach($object);
$subject->dispatch($event); // Event created above
```

### Async Handler Example

1.  Install a Queue Interop compatible transport, for example

    ```sh
    $ composer require enqueue/fs
    ```

    More Queue Interop compatible transport implementations can be found [here](https://packagist.org/packages/queue-interop/queue-interop).

2.  Webhook receiver (Web Server)

    ```php
    use Enqueue\Fs\FsConnectionFactory;
    use Fatindeed\GitlabWebhookHandler\EventSubject;

    $context = (new FsConnectionFactory)->createContext(); // Create a filesystem queue

    $subject = new EventSubject;
    $subject->dispatch($event, $context); // Event created above
    ```

3.  Daemon process (Run in background)

    ```php
    use Enqueue\Fs\FsConnectionFactory;
    use Fatindeed\GitlabWebhookHandler\EventSubject;

    $object = new YourWebhookHandler; // Replace with you webhook handler
    $context = (new FsConnectionFactory)->createContext(); // Create a filesystem queue

    $subject = new EventSubject;
    $subject->signalInstall(SIGTERM, [$subject, 'terminate']); // Alternative
    $subject->attach($object);
    $subject->run($context);
    ```

### With Monolog

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('application');
$logger->pushHandler(new StreamHandler(STDOUT, Logger::INFO));
$subject = new EventSubject($logger);
# code...
```

[ico-php-v]: https://img.shields.io/packagist/php-v/fatindeed/gitlab-webhook-handler.svg
[ico-version]: https://img.shields.io/packagist/v/fatindeed/gitlab-webhook-handler.svg
[ico-license]: https://img.shields.io/packagist/l/fatindeed/gitlab-webhook-handler.svg
[ico-travis]: https://img.shields.io/travis/fatindeed/gitlab-webhook-handler/master.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/fatindeed/gitlab-webhook-handler.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/fatindeed/gitlab-webhook-handler.svg
[ico-downloads]: https://img.shields.io/packagist/dm/fatindeed/gitlab-webhook-handler.svg

[link-packagist]: https://packagist.org/packages/fatindeed/gitlab-webhook-handler
[link-travis]: https://travis-ci.org/fatindeed/gitlab-webhook-handler
[link-scrutinizer]: https://scrutinizer-ci.com/g/fatindeed/gitlab-webhook-handler/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/fatindeed/gitlab-webhook-handler
[link-author]: https://github.com/fatindeed