<?php

use Vortos\Messenger\Consumer;
use Vortos\Messenger\Factory\RuntimeTransportFactory;
use Vortos\Messenger\Transport\Kafka\KafkaTransportFactory;
use Vortos\Persistence\Registry\DoctrineConnectionRegistry;
use Koco\Kafka\RdKafka\RdKafkaFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransportFactory;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\TransportFactory;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer as StandardSerializer;
use Symfony\Component\Messenger\Transport\Serialization\Serializer as MessengerSerializer;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(UidNormalizer::class)->tag('serializer.normalizer');
    $services->set(DateTimeNormalizer::class)->tag('serializer.normalizer');
    $services->set(ArrayDenormalizer::class)->tag('serializer.normalizer');
    $services->set(JsonEncoder::class)->tag('serializer.encoder');
    $services->set('property_info.extractor', ReflectionExtractor::class);

    $services->set(ObjectNormalizer::class)
        ->args([
            null,
            null,
            null,
            service('property_info.extractor')
        ])->tag('serializer.normalizer');

    $services->set('vortos.serializer.standard', StandardSerializer::class);

    $services->set('vortos.messenger.serializer', MessengerSerializer::class)
        ->args([
            service('vortos.serializer.standard') 
        ]);

    $services->set(RdKafkaFactory::class);

    $services->set('vortos.transport.factory.kafka', KafkaTransportFactory::class)
        ->args([
            service(RdKafkaFactory::class),
            service(LoggerInterface::class)->nullOnInvalid()
        ])
        ->tag('messenger.transport_factory');

    $services->set('vortos.transport.factory.amqp', AmqpTransportFactory::class)
        ->tag('messenger.transport_factory');

    $services->set('vortos.transport.factory.redis', RedisTransportFactory::class)
        ->tag('messenger.transport_factory');

    $services->set('vortos.transport.factory.doctrine', DoctrineTransportFactory::class)
        ->args([service(DoctrineConnectionRegistry::class)])
        ->tag('messenger.transport_factory');


    $services->set('vortos.transport.factory', RuntimeTransportFactory::class)
        ->args([
            service('messenger.transport_factory')
        ]);


    $services->set('messenger.transport_factory', TransportFactory::class)
        ->args([tagged_iterator('messenger.transport_factory')]);


    $services->set('messenger.sender_locator', SendersLocator::class);


    $services->set('vortos.transport.consumer', TransportInterface::class);

    $services->alias('vortos.consumer', Consumer::class)
        ->public();
    $services->alias(TransportInterface::class, 'vortos.transport.consumer' )
        ->public();
};
