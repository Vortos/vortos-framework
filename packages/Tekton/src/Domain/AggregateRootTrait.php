<?php

namespace Fortizan\Tekton\Domain;

trait AggregateRootTrait
{
    private array $recordedEvents = [];

    public function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function releaseEvents():array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }
}