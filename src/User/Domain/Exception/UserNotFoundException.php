<?php

namespace App\User\Domain\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(string $id):self
    {
        return new self("User not found with ID: $id");
    }
}