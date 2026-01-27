<?php

declare(strict_types=1);

namespace Fortizan\Tekton\Messenger\Transport\Kafka;

use Koco\Kafka\Messenger\KafkaSender;
use Koco\Kafka\Messenger\KafkaSenderProperties;
use Koco\Kafka\RdKafka\RdKafkaFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class KafkaTransport implements TransportInterface
{
    private KafkaSender $sender;
    private KafkaReceiver $receiver;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        RdKafkaFactory $rdKafkaFactory,
        KafkaSenderProperties $senderProperties,
        KafkaReceiverProperties $receiverProperties
    ) {
        $this->sender = new KafkaSender($logger, $serializer, $rdKafkaFactory, $senderProperties);
        $this->receiver = new KafkaReceiver($logger, $serializer, $rdKafkaFactory, $receiverProperties);
    }

    public function get(): iterable
    {
        return $this->receiver->get();
    }

    public function ack(Envelope $envelope): void
    {
        $this->receiver->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->receiver->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->sender->send($envelope);
    }
}
