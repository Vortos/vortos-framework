<?php

declare(strict_types=1);

namespace App\User\Application\EventHandler;

use Vortos\Messaging\Hook\Attribute\BeforeConsume;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;

#[BeforeConsume]
final class LogConsumeHook
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(Envelope $envelope, string $consumerName): void
    {
        $this->logger->info('Hook: BeforeConsume fired', [
            'event' => get_class($envelope->getMessage())
        ]);
    }
}
