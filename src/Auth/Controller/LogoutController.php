<?php

declare(strict_types=1);

namespace Vortos\Auth\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vortos\Auth\Attribute\RequiresAuth;
use Vortos\Auth\Identity\CurrentUserProvider;
use Vortos\Auth\Jwt\JwtService;

/**
 * Pre-built logout endpoint. Opt-in. Requires authentication.
 *
 * POST /api/auth/logout
 * Authorization: Bearer {accessToken}
 *
 * Revokes all refresh tokens for the authenticated user.
 * The access token remains technically valid until it expires (max 15 min)
 * but all refresh tokens are immediately invalidated — the user cannot
 * obtain new access tokens after logout.
 *
 * Returns 200 on success.
 * Returns 401 if no valid access token present.
 *
 * ## Access token revocation
 *
 * Access tokens cannot be revoked server-side without tracking every
 * issued token in Redis — which would require a lookup on every request.
 * The accepted trade-off in JWT authentication is that access tokens
 * remain valid until TTL expiry (15 min default). Keep access TTL short.
 *
 * For immediate access token invalidation (e.g. security incident),
 * use a token blacklist — add DbalTokenBlacklist to the backlog.
 */
#[Route('/api/auth/logout', methods: ['POST'])]
#[RequiresAuth]
final class LogoutController
{
    public function __construct(
        private JwtService $jwtService,
        private CurrentUserProvider $currentUser,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $identity = $this->currentUser->get();

        $this->jwtService->revokeAll($identity->id());

        return new JsonResponse(['message' => 'Logged out successfully']);
    }
}
