<?php

namespace App\User\Infrastructure\Service;

use App\User\Domain\Service\UserUniquenessCheckerInterface;
use Fortizan\Tekton\Persistence\Contract\SourceReaderInterface;

class DbalUserUniquenessChecker implements UserUniquenessCheckerInterface
{
    public function __construct(
        private SourceReaderInterface $sourceReader
    ){
    }

    public function isEmailUnique(string $email): bool
    {
        $query = "SELECT count(id) FROM users WHERE email = ?";
        $count = $this->sourceReader->fetchOne($query, [$email]);
        return $count === 0;
    }

    public function isMobileUnique(string $mobile): bool
    {
        $query = "SELECT count(id) FROM users WHERE mobile = ?";
        $count = $this->sourceReader->fetchOne($query, [$mobile]);
        return $count === 0;
    }
}