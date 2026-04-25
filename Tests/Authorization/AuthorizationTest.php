<?php

declare(strict_types=1);

namespace Tests\Authorization;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Vortos\Auth\Identity\AnonymousIdentity;
use Vortos\Auth\Identity\CurrentUserProvider;
use Vortos\Auth\Identity\UserIdentity;
use Vortos\Authorization\Attribute\AsPolicy;
use Vortos\Authorization\Attribute\RequiresPermission;
use Vortos\Authorization\Contract\PolicyInterface;
use Vortos\Authorization\Engine\PolicyEngine;
use Vortos\Authorization\Engine\PolicyRegistry;
use Vortos\Authorization\Exception\AccessDeniedException;
use Vortos\Authorization\Exception\PolicyNotFoundException;
use Vortos\Authorization\Middleware\AuthorizationMiddleware;
use Vortos\Authorization\Voter\RoleVoter;
use Vortos\Cache\Adapter\ArrayAdapter;

// ---- Stub policies for tests ----

#[AsPolicy(resource: 'articles')]
final class ArticlePolicy implements PolicyInterface
{
    public function supports(string $resource): bool
    {
        return $resource === 'articles';
    }

    public function can(UserIdentity|\Vortos\Auth\Contract\UserIdentityInterface $identity, string $action, string $scope, mixed $resource = null): bool
    {
        return match ($action) {
            'list'   => true,
            'read'   => true,
            'create' => $identity->hasRole('ROLE_EDITOR'),
            'update' => $scope === 'own'
                ? ($resource !== null && $resource === $identity->id())
                : $identity->hasRole('ROLE_ADMIN'),
            'delete' => $identity->hasRole('ROLE_ADMIN'),
            default  => false,
        };
    }
}

#[AsPolicy(resource: 'users')]
final class UserPolicy implements PolicyInterface
{
    public function supports(string $resource): bool
    {
        return $resource === 'users';
    }

    public function can(\Vortos\Auth\Contract\UserIdentityInterface $identity, string $action, string $scope, mixed $resource = null): bool
    {
        return match ($action) {
            'list'   => $identity->hasRole('ROLE_ADMIN'),
            'delete' => $identity->hasRole('ROLE_SUPER_ADMIN'),
            default  => false,
        };
    }
}

// ---- Stub controllers ----

#[RequiresPermission('articles.create.any')]
final class CreateArticleController
{
    public function __invoke(): Response
    {
        return new Response('created');
    }
}

#[RequiresPermission('articles.update.own', resourceParam: 'articleId')]
final class UpdateArticleController
{
    public function __invoke(): Response
    {
        return new Response('updated');
    }
}

#[RequiresPermission('articles.list.any')]
#[RequiresPermission('users.list.any')]
final class AdminDashboardController
{
    public function __invoke(): Response
    {
        return new Response('dashboard');
    }
}

final class PublicController
{
    public function __invoke(): Response
    {
        return new Response('public');
    }
}

final class AuthorizationTest extends TestCase
{
    private PolicyEngine $engine;
    private PolicyRegistry $registry;
    private RoleVoter $roleVoter;
    private ArrayAdapter $arrayAdapter;
    private HttpKernelInterface $stubKernel;

    protected function setUp(): void
    {
        $articlePolicy = new ArticlePolicy();
        $userPolicy = new UserPolicy();

        $locator = new ServiceLocator([
            'articles' => fn() => $articlePolicy,
            'users'    => fn() => $userPolicy,
        ]);

        $this->registry = new PolicyRegistry($locator);
        $this->engine = new PolicyEngine($this->registry);

        $this->roleVoter = new RoleVoter([
            'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
            'ROLE_ADMIN'       => ['ROLE_EDITOR'],
            'ROLE_EDITOR'      => ['ROLE_USER'],
        ]);

        $this->arrayAdapter = new ArrayAdapter();

        $this->stubKernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response('ok', 200);
            }
        };
    }

    protected function tearDown(): void
    {
        $this->arrayAdapter->clear();
    }

    private function makeEvent(string $controllerClass, string $identity = 'authenticated', array $routeParams = []): RequestEvent
    {
        $request = Request::create('/test');
        $request->attributes->set('_controller', $controllerClass);

        foreach ($routeParams as $key => $value) {
            $request->attributes->set($key, $value);
        }

        if ($identity === 'anonymous') {
            $this->arrayAdapter->set('auth:identity', new AnonymousIdentity());
        } elseif ($identity === 'admin') {
            $this->arrayAdapter->set('auth:identity', new UserIdentity('admin-user', ['ROLE_ADMIN']));
        } elseif ($identity === 'editor') {
            $this->arrayAdapter->set('auth:identity', new UserIdentity('editor-user', ['ROLE_EDITOR']));
        } elseif ($identity === 'user') {
            $this->arrayAdapter->set('auth:identity', new UserIdentity('regular-user', ['ROLE_USER']));
        } elseif ($identity === 'super_admin') {
            $this->arrayAdapter->set('auth:identity', new UserIdentity('super-user', ['ROLE_SUPER_ADMIN']));
        } else {
            // Default authenticated with specific ID
            $this->arrayAdapter->set('auth:identity', new UserIdentity($identity, ['ROLE_USER']));
        }

        return new RequestEvent($this->stubKernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    private function makeMiddleware(): AuthorizationMiddleware
    {
        return new AuthorizationMiddleware(
            $this->engine,
            new CurrentUserProvider($this->arrayAdapter),
        );
    }

    // -------------------------------------------------------------------------
    // POLICY ENGINE — can() tests
    // -------------------------------------------------------------------------

    public function test_can_returns_false_for_anonymous_user(): void
    {
        $this->assertFalse($this->engine->can(new AnonymousIdentity(), 'articles.create.any'));
    }

    public function test_can_returns_true_when_policy_allows(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_EDITOR']);
        $this->assertTrue($this->engine->can($identity, 'articles.create.any'));
    }

    public function test_can_returns_false_when_policy_denies(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_USER']);
        $this->assertFalse($this->engine->can($identity, 'articles.create.any'));
    }

    public function test_can_returns_false_for_unknown_resource(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_ADMIN']);
        $this->assertFalse($this->engine->can($identity, 'competitions.create.any'));
    }

    public function test_can_returns_false_for_invalid_permission_format(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_ADMIN']);
        $this->assertFalse($this->engine->can($identity, 'invalid-format'));
    }

    public function test_can_passes_resource_to_policy_for_scope_check(): void
    {
        $identity = new UserIdentity('user-owner', ['ROLE_USER']);

        // Own resource — should pass
        $this->assertTrue($this->engine->can($identity, 'articles.update.own', 'user-owner'));

        // Someone else's resource — should fail
        $this->assertFalse($this->engine->can($identity, 'articles.update.own', 'other-user'));
    }

    // -------------------------------------------------------------------------
    // POLICY ENGINE — authorize() tests
    // -------------------------------------------------------------------------

    public function test_authorize_throws_for_anonymous_user(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->engine->authorize(new AnonymousIdentity(), 'articles.create.any');
    }

    public function test_authorize_throws_forbidden_for_authenticated_user_without_permission(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_USER']);

        $this->expectException(AccessDeniedException::class);
        $this->engine->authorize($identity, 'articles.create.any');
    }

    public function test_authorize_does_not_throw_when_allowed(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_ADMIN']);

        $this->expectNotToPerformAssertions();
        $this->engine->authorize($identity, 'articles.delete.any');
    }

    // -------------------------------------------------------------------------
    // POLICY REGISTRY tests
    // -------------------------------------------------------------------------

    public function test_registry_finds_policy_for_known_resource(): void
    {
        $policy = $this->registry->findForResource('articles');
        $this->assertInstanceOf(ArticlePolicy::class, $policy);
    }

    public function test_registry_throws_for_unknown_resource(): void
    {
        $this->expectException(PolicyNotFoundException::class);
        $this->registry->findForResource('nonexistent');
    }

    public function test_registry_has_for_resource_returns_true(): void
    {
        $this->assertTrue($this->registry->hasForResource('articles'));
        $this->assertFalse($this->registry->hasForResource('nonexistent'));
    }

    // -------------------------------------------------------------------------
    // ROLE VOTER tests
    // -------------------------------------------------------------------------

    public function test_role_voter_exact_role_match(): void
    {
        $identity = new UserIdentity('user-1', ['ROLE_EDITOR']);
        $this->assertTrue($this->roleVoter->hasRole($identity, 'ROLE_EDITOR'));
        $this->assertFalse($this->roleVoter->hasRole($identity, 'ROLE_ADMIN'));
    }

    public function test_role_voter_hierarchy_expansion(): void
    {
        // ROLE_SUPER_ADMIN inherits ROLE_ADMIN which inherits ROLE_EDITOR which inherits ROLE_USER
        $identity = new UserIdentity('super', ['ROLE_SUPER_ADMIN']);

        $this->assertTrue($this->roleVoter->hasRole($identity, 'ROLE_SUPER_ADMIN'));
        $this->assertTrue($this->roleVoter->hasRole($identity, 'ROLE_ADMIN'));
        $this->assertTrue($this->roleVoter->hasRole($identity, 'ROLE_EDITOR'));
        $this->assertTrue($this->roleVoter->hasRole($identity, 'ROLE_USER'));
    }

    public function test_role_voter_at_least(): void
    {
        $admin = new UserIdentity('admin', ['ROLE_ADMIN']);
        $user = new UserIdentity('user', ['ROLE_USER']);

        $this->assertTrue($this->roleVoter->atLeast($admin, 'ROLE_EDITOR')); // admin inherits editor
        $this->assertFalse($this->roleVoter->atLeast($user, 'ROLE_EDITOR')); // user does not inherit editor
    }

    public function test_role_voter_has_any(): void
    {
        $identity = new UserIdentity('user', ['ROLE_EDITOR']);

        $this->assertTrue($this->roleVoter->hasAny($identity, ['ROLE_ADMIN', 'ROLE_EDITOR']));
        $this->assertFalse($this->roleVoter->hasAny($identity, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']));
    }

    public function test_role_voter_has_all(): void
    {
        $identity = new UserIdentity('user', ['ROLE_EDITOR', 'ROLE_USER']);

        $this->assertTrue($this->roleVoter->hasAll($identity, ['ROLE_EDITOR', 'ROLE_USER']));
        $this->assertFalse($this->roleVoter->hasAll($identity, ['ROLE_EDITOR', 'ROLE_ADMIN']));
    }

    public function test_role_voter_with_empty_hierarchy(): void
    {
        $voter = new RoleVoter([]);
        $identity = new UserIdentity('user', ['ROLE_ADMIN']);

        $this->assertTrue($voter->hasRole($identity, 'ROLE_ADMIN'));
        $this->assertFalse($voter->hasRole($identity, 'ROLE_USER'));
    }

    // -------------------------------------------------------------------------
    // AUTHORIZATION MIDDLEWARE tests
    // -------------------------------------------------------------------------

    public function test_public_route_passes_through(): void
    {
        $middleware = $this->makeMiddleware();
        $event = $this->makeEvent(PublicController::class, 'admin');

        $middleware->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function test_protected_route_anonymous_user_returns_401(): void
    {
        $middleware = $this->makeMiddleware();
        $event = $this->makeEvent(CreateArticleController::class, 'anonymous');

        $middleware->onKernelRequest($event);

        $this->assertNotNull($event->getResponse());
        $this->assertEquals(401, $event->getResponse()->getStatusCode());
    }

    public function test_protected_route_unauthorized_user_returns_403(): void
    {
        $middleware = $this->makeMiddleware();
        $event = $this->makeEvent(CreateArticleController::class, 'user'); // ROLE_USER cannot create

        $middleware->onKernelRequest($event);

        $this->assertNotNull($event->getResponse());
        $this->assertEquals(403, $event->getResponse()->getStatusCode());
    }

    public function test_protected_route_authorized_user_passes(): void
    {
        $middleware = $this->makeMiddleware();
        $event = $this->makeEvent(CreateArticleController::class, 'editor'); // ROLE_EDITOR can create

        $middleware->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function test_403_response_contains_permission_in_body(): void
    {
        $middleware = $this->makeMiddleware();
        $event = $this->makeEvent(CreateArticleController::class, 'user');

        $middleware->onKernelRequest($event);

        $body = json_decode($event->getResponse()->getContent(), true);
        $this->assertArrayHasKey('permission', $body);
        $this->assertEquals('articles.create.any', $body['permission']);
        $this->assertEquals('Forbidden', $body['error']);
    }

    public function test_ownership_scope_check_with_matching_resource(): void
    {
        $middleware = $this->makeMiddleware();
        // User is 'regular-user', articleId matches their ID
        $event = $this->makeEvent(
            UpdateArticleController::class,
            'regular-user',
            ['articleId' => 'regular-user'],
        );

        $middleware->onKernelRequest($event);

        $this->assertNull($event->getResponse()); // allowed — owns the resource
    }

    public function test_ownership_scope_check_with_non_matching_resource(): void
    {
        $middleware = $this->makeMiddleware();
        // User is 'regular-user', articleId belongs to someone else
        $event = $this->makeEvent(
            UpdateArticleController::class,
            'regular-user',
            ['articleId' => 'other-user'],
        );

        $middleware->onKernelRequest($event);

        $this->assertEquals(403, $event->getResponse()->getStatusCode());
    }

    public function test_multiple_permissions_all_must_pass(): void
    {
        $middleware = $this->makeMiddleware();

        // AdminDashboardController requires articles.list.any AND users.list.any
        // ROLE_ADMIN can list articles (any authenticated can list articles)
        // but only ROLE_ADMIN can list users
        $event = $this->makeEvent(AdminDashboardController::class, 'admin');

        $middleware->onKernelRequest($event);

        $this->assertNull($event->getResponse()); // admin passes both
    }

    public function test_multiple_permissions_fails_if_one_denied(): void
    {
        $middleware = $this->makeMiddleware();

        // ROLE_EDITOR can list articles but CANNOT list users
        $event = $this->makeEvent(AdminDashboardController::class, 'editor');

        $middleware->onKernelRequest($event);

        $this->assertEquals(403, $event->getResponse()->getStatusCode());
    }

    public function test_subrequest_is_skipped(): void
    {
        $middleware = $this->makeMiddleware();

        $request = Request::create('/test');
        $request->attributes->set('_controller', CreateArticleController::class);

        $event = new RequestEvent(
            $this->stubKernel,
            $request,
            HttpKernelInterface::SUB_REQUEST, // subrequest
        );

        $this->arrayAdapter->set('auth:identity', new AnonymousIdentity());

        $middleware->onKernelRequest($event);

        $this->assertNull($event->getResponse()); // skipped
    }

    public function test_permission_format_parsing(): void
    {
        [$resource, $action, $scope] = $this->engine->parsePermission('athletes.update.own');

        $this->assertEquals('athletes', $resource);
        $this->assertEquals('update', $action);
        $this->assertEquals('own', $scope);
    }

    public function test_invalid_permission_format_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->engine->parsePermission('invalid-format');
    }
}
