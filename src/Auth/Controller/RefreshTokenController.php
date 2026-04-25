<?php

declare(strict_types=1);

namespace Vortos\Auth\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vortos\Auth\Contract\UserProviderInterface;
use Vortos\Auth\Exception\AuthenticationException;
use Vortos\Auth\Identity\UserIdentity;
use Vortos\Auth\Jwt\JwtService;

/**
 * Pre-built token refresh endpoint. Opt-in.
 *
 * POST /api/auth/refresh
 * Body: { "refresh_token": "..." }
 *
 * Validates the refresh token, revokes it, issues a new pair.
 * This is token rotation — the old refresh token is immediately invalidated.
 *
 * Returns new JwtToken pair on success.
 * Returns 401 on invalid, expired, or already-used refresh token.
 * Returns 422 on missing refresh_token field.
 *
 * ## Why we reload the user
 *
 * The refresh token contains the user ID but not current roles.
 * Roles may have changed since the token was issued (e.g. user promoted to admin).
 * We reload the user from UserProviderInterface to get fresh roles for the new token.
 * If your roles never change mid-session, you can optimise by embedding roles
 * in the refresh token — but the safe default is always reload.
 */
#[Route('/api/auth/refresh', methods: ['POST'])]
final class RefreshTokenController
{
    public function __construct(
        private JwtService $jwtService,
        private UserProviderInterface $userProvider,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $refreshToken = trim($data['refresh_token'] ?? '');

        if ($refreshToken === '') {
            return new JsonResponse(
                ['error' => 'Validation failed', 'fields' => ['refresh_token' => 'required']],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        try {
            // Decode refresh token to get user ID — do not validate as access token
            $userId = $this->jwtService->getUserIdFromRefreshToken($refreshToken);

            $user = $this->userProvider->findById($userId);

            if ($user === null) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            $identity = new UserIdentity(id: $user->getId(), roles: $user->getRoles());

            $newToken = $this->jwtService->refresh($refreshToken, $identity);

            return new JsonResponse($newToken->toArray());
        } catch (AuthenticationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }
}
