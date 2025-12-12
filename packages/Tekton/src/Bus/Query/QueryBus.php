<?php

namespace Fortizan\Tekton\Bus\Query;

use Fortizan\Tekton\Bus\Query\Contract\QueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class QueryBus
{

    public function __construct(
        private MessageBusInterface $queryBus
    ) {}

    public function ask(QueryInterface $query): Response
    {
        $envelop = $this->queryBus->dispatch(message: $query);
        
        $stamp = $envelop->last(HandledStamp::class);

        $result = $stamp->getResult();

        return new Response(new JsonResponse($result));
    }
}
