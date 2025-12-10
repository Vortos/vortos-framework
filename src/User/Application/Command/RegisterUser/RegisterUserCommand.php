<?php

namespace App\User\Application\Command\RegisterUser;

use Fortizan\Tekton\Interface\CommandInterface;

readonly class RegisterUserCommand implements CommandInterface
{
    public function __construct(
        public string $firstName,
        public string $email
    )
    {}
}