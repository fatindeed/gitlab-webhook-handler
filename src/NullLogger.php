<?php

namespace Fatindeed\GitlabWebhookHandler;

use Psr\Log\AbstractLogger;

class NullLogger extends AbstractLogger
{
    /**
     * @var string
     */
    private $content = '';

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->content .= $message;
    }

    /**
     * Get content
     * 
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
