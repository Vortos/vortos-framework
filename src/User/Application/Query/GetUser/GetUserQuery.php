<?php

namespace App\User\Application\Query\GetUser;

use Fortizan\Tekton\Bus\Query\Contract\QueryInterface;

class GetUserQuery implements QueryInterface
{
    public function __construct(
        public string $userId
    )
    {}
}