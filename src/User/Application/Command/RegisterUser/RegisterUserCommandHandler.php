<?php

declare(strict_types=1);

namespace App\User\Application\Command\RegisterUser;

use App\User\Domain\Entity\User;
use Vortos\Cqrs\Attribute\AsCommandHandler;

#[AsCommandHandler(handles:RegisterUserCommand::class)]
final class RegisterUserHandler 
{
    public function __invoke(RegisterUserCommand $command): User
    {
        $user = User::registerUser(
            $command->name,
            $command->email,
            true
        );

        // No repository yet — just return the aggregate
        return $user;
    }
}
