<?php

namespace Fortizan\Tekton\Messenger\Transport\Kafka\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class KafkaTopicStamp implements StampInterface
{
    public function __construct(
        private string $topic
    ){
    }

    public function getTopic():string
    {
        return $this->topic;
    }
}