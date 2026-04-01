<?php

namespace App\User\Application\Command\RegisterUser;

use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserAlreadyExistException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Service\UserUniquenessCheckerInterface;
use Vortos\Bus\Command\Attribute\CommandHandler;

#[CommandHandler]
class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserUniquenessCheckerInterface $userUniquenessChecker
    ){
    }

    public function __invoke(RegisterUserCommand $command)
    {
        // if(!$this->userUniquenessChecker->isEmailUnique($command->email)){
        //     throw UserAlreadyExistException::withEmail($command->email);
        // }

        $user = User::registerUser(
            $command->name,
            $command->email,
            'ACTIVE'
        );

        $this->userRepository->save($user);
    }
}
