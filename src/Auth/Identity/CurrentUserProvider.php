<?php

declare(strict_types=1);

namespace Vortos\Auth\Identity;

use Vortos\Auth\Contract\UserIdentityInterface;
use Vortos\Cache\Adapter\ArrayAdapter;

/**
 * Retrieves the current user identity from the request-scoped ArrayAdapter.
 *
 * Inject this into any service that needs to know the current user.
 * The identity is set by AuthMiddleware at the start of every request.
 *
 * ## Usage
 *
 *   final class SomeHandler
 *   {
 *       public function __construct(private CurrentUserProvider $currentUser) {}
 *
 *       public function __invoke(SomeCommand $command): void
 *       {
 *           $user = $this->currentUser->get();
 *
 *           if (!$user->isAuthenticated()) {
 *               throw new UnauthorizedException();
 *           }
 *
 *           if (!$user->hasRole('ROLE_ADMIN')) {
 *               throw new ForbiddenException();
 *           }
 *       }
 *   }
 *
 * ## Always returns an identity
 *
 * get() never returns null. For unauthenticated requests it returns AnonymousIdentity.
 * Always check isAuthenticated() before using the identity.
 */
final class CurrentUserProvider
{
    public function __construct(private ArrayAdapter $arrayAdapter) {}

    /**
     * Get the current user identity.
     * Returns AnonymousIdentity for unauthenticated requests.
     */
    public function get(): UserIdentityInterface
    {
        $identity = $this->arrayAdapter->get('auth:identity');

        if ($identity instanceof UserIdentityInterface) {
            return $identity;
        }

        return new AnonymousIdentity();
    }
}
