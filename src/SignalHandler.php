<?php

namespace Fatindeed\GitlabWebhookHandler;

/**
 * SignalHandler trait
 */
trait SignalHandler
{
    private $pcntl_loaded = false;

    /**
     * Installs a signal handler
     * 
     * @param  int $signo
     * @param  callable|int $handler
     * @return bool
     */
    public function signalInstall(int $signo, $handler): bool
    {
        if (extension_loaded('pcntl')) {
            $this->pcntl_loaded = true;
            return pcntl_signal($signo, $handler);
        } else {
            return false; // @codeCoverageIgnore
        }
    }

    /**
     * Calls signal handlers for pending signals
     * 
     * @return bool
     */
    public function signalDispatch(): bool
    {
        return $this->pcntl_loaded && pcntl_signal_dispatch();
    }
}
