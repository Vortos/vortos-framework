<?php

declare(strict_types=1);

namespace Vortos\Messaging\Runtime;

use Vortos\Messaging\Outbox\OutboxRelayWorker;

/**
 * Runs the outbox relay loop continuously.
 * 
 * When the relay worker returns 0 (nothing to relay), sleeps for sleepMs
 * milliseconds before polling again to avoid hammering the database.
 * When a full batch is returned, loops immediately — more messages may be waiting.
 * Exits cleanly when stop() is called, e.g. by a SIGTERM signal handler.
 */
final class OutboxRelayRunner
{
    private bool $running = false;

    public function __construct(
        private OutboxRelayWorker $worker
    ){
    }

    public function run(int $batchSize, int $sleepMs) :void
    {
        $this->running = true;

        while ($this->running) {
            $relayed = $this->worker->relay($batchSize);

            if($relayed === 0){
                usleep($sleepMs * 1000);
            }
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }
}