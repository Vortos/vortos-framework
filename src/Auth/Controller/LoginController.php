<?php

declare(strict_types=1);

namespace Vortos\Auth\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vortos\Auth\Contract\PasswordHasherInterface;
use Vortos\Auth\Contract\UserProviderInterface;
use Vortos\Auth\Identity\UserIdentity;
use Vortos\Auth\Jwt\JwtService;

/**
 * Pre-built login endpoint. Opt-in — enable in config/auth.php:
 *
 *   $config->enableBuiltInControllers(true);
 *
 * POST /api/auth/login
 * Body: { "email": "...", "password": "..." }
 *
 * Returns JwtToken pair on success.
 * Returns 401 on invalid credentials.
 * Returns 422 on missing fields.
 *
 * ## Customisation
 *
 * If this controller does not fit your needs, disable it and write your own.
 * The only difference from a custom controller is the route — all the services
 * (JwtService, PasswordHasherInterface) are still available for injection.
 *
 * ## UserProviderInterface
 *
 * You must implement UserProviderInterface and register it as a service.
 * This is the bridge between the auth module and your User aggregate.
 * See UserProviderInterface for the contract.
 */
#[Route('/api/auth/login', methods: ['POST'])]
final class LoginController
{
    public function __construct(
        private JwtService $jwtService,
        private PasswordHasherInterface $hasher,
        private UserProviderInterface $userProvider,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            return new JsonResponse(
                ['error' => 'Validation failed', 'fields' => ['email' => 'required', 'password' => 'required']],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $user = $this->userProvider->findByEmail($email);

        if ($user === null || !$this->hasher->verify($password, $user->getPasswordHash())) {
            return new JsonResponse(
                ['error' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED,
            );
        }

        if ($this->hasher->needsRehash($user->getPasswordHash())) {
            $this->userProvider->updatePasswordHash(
                $user->getId(),
                $this->hasher->hash($password),
            );
        }

        $identity = new UserIdentity(
            id: $user->getId(),
            roles: $user->getRoles(),
        );

        return new JsonResponse($this->jwtService->issue($identity)->toArray());
    }
}
