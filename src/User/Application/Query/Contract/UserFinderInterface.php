<?php

namespace App\User\Application\Query\Contract;

interface UserFinderInterface
{
    public function findById(string $id):array;
    public function findByEmail(string $email):array;
}