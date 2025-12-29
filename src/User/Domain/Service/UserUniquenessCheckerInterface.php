<?php

namespace App\User\Domain\Service;

interface UserUniquenessCheckerInterface
{
    public function isEmailUnique(string $email):bool;
    public function isMobileUnique(string $mobile):bool;
}