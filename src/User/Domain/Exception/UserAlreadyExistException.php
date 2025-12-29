<?php

namespace App\User\Domain\Exception;

use Exception;

class UserAlreadyExistException extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withEmail(string $email):self
    {
        return new self("A user with email {$email} already exists");
    }
}