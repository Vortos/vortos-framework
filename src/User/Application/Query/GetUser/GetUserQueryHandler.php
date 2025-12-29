<?php

namespace App\User\Application\Query\GetUser;

use App\User\Application\Query\Contract\UserFinderInterface;
use Fortizan\Tekton\Bus\Query\Attribute\QueryHandler;

#[QueryHandler]
class GetUserQueryHandler
{
    public function __construct(
        private UserFinderInterface $userFinder
    ) {}

    public function __invoke(GetUserQuery $query): GetUserResponse
    {
        $user = $this->userFinder->findById($query->userId);

        return new GetUserResponse(
            userId: $user['_id'],
            userEmail: $user['email'],
            userName: $user["name"]
        );
    }
}
