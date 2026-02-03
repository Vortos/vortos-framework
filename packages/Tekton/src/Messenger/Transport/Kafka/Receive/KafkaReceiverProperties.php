<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messenger\Transport\Kafka\Receive;

use RdKafka\Conf as KafkaConf;

final class KafkaReceiverProperties
{

    public function __construct(
        private KafkaConf $kafkaConf,
        private string|array $topicName,
        private int $receiveTimeoutMs,
        private bool $commitAsync,
        private int $batchSize,
    ) {}

    public function getKafkaConf(): KafkaConf
    {
        return $this->kafkaConf;
    }

    public function getTopicName(): string|array
    {
        return $this->topicName;
    }

    public function getReceiveTimeoutMs(): int
    {
        return $this->receiveTimeoutMs;
    }

    public function isCommitAsync(): bool
    {
        return $this->commitAsync;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }
}
