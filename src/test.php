<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Koco\Kafka\Messenger\KafkaTransportFactory;
use Koco\Kafka\RdKafka\RdKafkaFactory;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

$dotEnv = new Dotenv();
$dotEnv->load(__DIR__. "/../.env");

$groupId = $argv[1];
if(!$groupId){
    throw new InvalidArgumentException("Invalid kafka consumer group id");
}

$container = include __DIR__ . "/../packages/Vortos/src/Container/Container.php";
$container->setParameter('message.consumer.group.id', $groupId);
$container->compile();

$dispatcher = new EventDispatcher();

$serilizer = new Serializer();
$logger = new Logger('kafka');
$rdkafkaFactory = new RdKafkaFactory();
$kafkaFactory = new KafkaTransportFactory($rdkafkaFactory, $logger);


$kafkaTransport = $kafkaFactory->createTransport(
    $_ENV['MESSENGER_TRANSPORT_DSN'],
    [
        'topic' => ['name' => 'events'],
        'kafka_conf' => [
            'group.id' => 'vortos-consumer',
            'auto.offset.reset' => 'earliest'
        ]
    ],
    $serilizer
);

$evelops = $kafkaTransport->get();
var_dump(iterator_to_array($evelops));
