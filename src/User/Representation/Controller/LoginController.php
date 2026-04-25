<?php

declare(strict_types=1);

namespace App\User\Representation\Controller;

use App\User\Domain\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Vortos\Auth\Contract\PasswordHasherInterface;
use Vortos\Auth\Identity\UserIdentity;
use Vortos\Auth\Jwt\JwtService;
use Vortos\Http\Attribute\ApiController;

#[ApiController]
#[Route('/api/auth/login', methods: ['POST'])]
final class LoginController
{
    public function __construct(
        private JwtService $jwtService,
        private PasswordHasherInterface $hasher,
        // private UserRepository $userRepository,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::registerUser('fdsf', 'dsfs', '');

        // if ($user === null || !$this->hasher->verify($password, $user->getPasswordHash())) {
        //     return new JsonResponse(
        //         ['error' => 'Invalid credentials'],
        //         Response::HTTP_UNAUTHORIZED,
        //     );
        // }

        $identity = new UserIdentity(
            id: (string) $user->getId(),
            roles: ['admin'],
        );

        $token = $this->jwtService->issue($identity);

        return new JsonResponse($token->toArray());
    }
}
