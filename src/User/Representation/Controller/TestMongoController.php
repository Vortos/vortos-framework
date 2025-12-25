<?php

namespace App\User\Representation\Controller;

use App\User\Infrastructure\Query\MongoUserFinder;
use Fortizan\Tekton\Attribute\ApiController;
use Fortizan\Tekton\Database\DatabaseManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ApiController]
#[Route(name:'user.mongo', path:'/user/mongo')]
class TestMongoController
{
    public function __construct(
        private DatabaseManager $db
    ){
    }

    public function __invoke():Response
    {
        $this->db->read()->delete('user', 1);    

        $this->db->read()->save('user', [
            '_id' => 1,
            'name' => 'sachintha',
            'email' => 'abc@gmail.com',
            'roles' => ['Admin', 'User']
        ]);    

        $user = $this->db->read()->find('user', 1);

        return new JsonResponse($user);

    }
}