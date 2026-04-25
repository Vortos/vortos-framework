<?php

declare(strict_types=1);

namespace Vortos\Auth\Attribute;

use Attribute;

/**
 * Injects the current user identity into a controller constructor parameter.
 *
 * Future use — for now, inject CurrentUserProvider directly.
 * Planned: ArgumentValueResolver that resolves #[CurrentUser] parameters
 * automatically from the ArrayAdapter.
 *
 * ## Planned usage (not yet implemented)
 *
 *   public function __invoke(
 *       Request $request,
 *       #[CurrentUser] UserIdentityInterface $user,
 *   ): JsonResponse
 *
 * ## Current usage
 *
 * Inject CurrentUserProvider and call get():
 *
 *   public function __construct(private CurrentUserProvider $currentUser) {}
 *
 *   public function __invoke(Request $request): JsonResponse
 *   {
 *       $user = $this->currentUser->get();
 *       if (!$user->isAuthenticated()) { ... }
 *   }
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class CurrentUser {}
