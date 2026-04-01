<?php

declare(strict_types=1);

namespace App\User\Application\EventHandler;

use Vortos\Messaging\Attribute\AsMiddleware;
use Vortos\Messaging\Middleware\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;

#[AsMiddleware(priority: 100)]
final class TestMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(Envelope $envelope, callable $next): Envelope
    {
        $this->logger->info('TestMiddleware BEFORE', [
            'event' => get_class($envelope->getMessage())
        ]);

        $result = $next($envelope);

        $this->logger->info('TestMiddleware AFTER', [
            'event' => get_class($envelope->getMessage())
        ]);

        return $result;
    }
}
