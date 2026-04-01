<?php

namespace Vortos\Bus\Query;

use Vortos\Bus\Query\Contract\QueryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class QueryBus
{

    public function __construct(
        private MessageBusInterface $queryBus
    ) {}

    public function ask(QueryInterface $query): mixed
    {
        $envelop = $this->queryBus->dispatch(message: $query);
        
        $stamp = $envelop->last(HandledStamp::class);

        $result = $stamp->getResult();

        return $result;
    }
}
