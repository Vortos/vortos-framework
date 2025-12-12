<?php

namespace App\User\Application\Query\GetUser;

use Fortizan\Tekton\Bus\Query\Attribute\QueryHandler;

#[QueryHandler]
class GetUserQueryHandler
{

    public function __invoke(GetUserQuery $query): GetUserResponse
    {
        return new GetUserResponse(
            userId: $query->userId,
            userEmail: "abc@gmail.com",
            userName: "tekton"
        );
    }
}
