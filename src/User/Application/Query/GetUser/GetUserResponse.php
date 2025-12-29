<?php

namespace App\User\Application\Query\GetUser;

use JsonSerializable;

readonly class GetUserResponse implements JsonSerializable 
{
    public function __construct(
        private string $userId,
        private string $userName,
        private string $userEmail
    )
    {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->userId,
            'email' => $this->userEmail,
            'name' => $this->userName
        ];
    }
}