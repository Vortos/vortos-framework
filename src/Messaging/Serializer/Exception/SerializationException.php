<?php

declare(strict_types=1);

namespace Vortos\Messaging\Serializer\Exception;

use RuntimeException;

/**
 * Thrown when a domain event cannot be serialized to wire format.
 * Include the event class name in the message when throwing.
 */
final class SerializationException extends RuntimeException
{

}
