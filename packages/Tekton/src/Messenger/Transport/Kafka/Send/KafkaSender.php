<?php

namespace Fortizan\Tekton\Messenger\Transport\Kafka\Send;

use Fortizan\Tekton\Messenger\Transport\Kafka\Stamp\KafkaTopicStamp;
use Koco\Kafka\RdKafka\RdKafkaFactory;
use Psr\Log\LoggerInterface;
use RdKafka\Producer as KafkaProducer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class KafkaSender implements SenderInterface
{
    private KafkaProducer $producer;
    private bool $isSubscribed = false;

    public function __construct(
        private KafkaSenderProperties $properties,
        private SerializerInterface $serializer,
        private RdKafkaFactory $rdkafkaFactory,
        private LoggerInterface $logger
    ) {}

    public function send(Envelope $envelope): Envelope
    {
        $topicStamp = $envelope->last(KafkaTopicStamp::class);

        $topic = $this->properties->getTopicName()[0];
        if ($topicStamp !== null) {
            $topic = $topicStamp->getTopic();
        }

        return $envelope;
    }

    private function getProducer(): KafkaProducer
    {
        return $this->producer ?? $this->producer = $this->rdkafkaFactory->createProducer($this->properties->getKafkaConf());
    }
}
