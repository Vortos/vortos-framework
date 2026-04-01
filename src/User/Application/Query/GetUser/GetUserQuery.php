<?php

namespace App\User\Application\Query\GetUser;

use Vortos\Bus\Query\Contract\QueryInterface;

class GetUserQuery implements QueryInterface
{
    public function __construct(
        public string $userId
    )
    {}
}