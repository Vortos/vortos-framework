<?php

namespace App\User\Representation\Controller;

use App\User\Domain\Event\UserCreatedEvent;
use Fortizan\Tekton\Attribute\ApiController;
use Fortizan\Tekton\Messaging\Contract\EventBusInterface;
use Fortizan\Tekton\Messaging\Driver\InMemory\Runtime\InMemoryBroker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\UuidV7;

#[ApiController]
#[Route('/test/inmemory', methods: ['GET'])]
final class TestInMemoryController
{
    public function __construct(
        private EventBusInterface $eventBus,
        private InMemoryBroker $broker,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->eventBus->dispatch(new UserCreatedEvent(
            id: new UuidV7(),
            name: 'test-123',
            email: 'test@example.com',
        ));

        $messages = $this->broker->all('user.events');

        return new JsonResponse([
            'messages_in_broker' => count($messages),
            'payload' => $messages[0]->payload ?? null,
        ]);
    }
}