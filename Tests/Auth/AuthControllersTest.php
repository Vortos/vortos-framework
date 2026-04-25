<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Vortos\Auth\Contract\AuthUserInterface;
use Vortos\Auth\Contract\PasswordHasherInterface;
use Vortos\Auth\Contract\UserProviderInterface;
use Vortos\Auth\Controller\LoginController;
use Vortos\Auth\Controller\LogoutController;
use Vortos\Auth\Controller\RefreshTokenController;
use Vortos\Auth\Hasher\ArgonPasswordHasher;
use Vortos\Auth\Identity\CurrentUserProvider;
use Vortos\Auth\Identity\UserIdentity;
use Vortos\Auth\Jwt\JwtConfig;
use Vortos\Auth\Jwt\JwtService;
use Vortos\Auth\Storage\InMemoryTokenStorage;
use Vortos\Cache\Adapter\ArrayAdapter;

// Stub auth user
final class StubAuthUser implements AuthUserInterface
{
    public function __construct(
        private string $id,
        private string $passwordHash,
        private array $roles = ['ROLE_USER'],
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function getRoles(): array
    {
        return $this->roles;
    }
}

// Stub user provider
final class StubUserProvider implements UserProviderInterface
{
    private array $users = [];

    public function addUser(string $email, AuthUserInterface $user): void
    {
        $this->users[$email] = $user;
    }

    public function findByEmail(string $email): ?AuthUserInterface
    {
        return $this->users[$email] ?? null;
    }

    public function findById(string $id): ?AuthUserInterface
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) return $user;
        }
        return null;
    }

    public function updatePasswordHash(string $id, string $hash): void {}
}

final class AuthControllersTest extends TestCase
{
    private JwtService $jwtService;
    private InMemoryTokenStorage $tokenStorage;
    private ArgonPasswordHasher $hasher;
    private StubUserProvider $userProvider;
    private ArrayAdapter $arrayAdapter;

    protected function setUp(): void
    {
        $config = new JwtConfig(
            secret: 'test-secret-at-least-32-characters-long',
            accessTokenTtl: 900,
            refreshTokenTtl: 604800,
            issuer: 'test',
        );
        $this->tokenStorage = new InMemoryTokenStorage();
        $this->jwtService = new JwtService($config, $this->tokenStorage);
        $this->hasher = new ArgonPasswordHasher(memoryCost: 1024, timeCost: 1);
        $this->userProvider = new StubUserProvider();
        $this->arrayAdapter = new ArrayAdapter();

        // Register a test user
        $hash = $this->hasher->hash('secret123');
        $this->userProvider->addUser('alice@example.com', new StubAuthUser(
            id: 'user-alice',
            passwordHash: $hash,
            roles: ['ROLE_USER', 'ROLE_ADMIN'],
        ));
    }

    protected function tearDown(): void
    {
        $this->tokenStorage->clear();
        $this->arrayAdapter->clear();
    }

    // -------------------------------------------------------------------------
    // LOGIN CONTROLLER TESTS
    // -------------------------------------------------------------------------

    public function test_login_with_valid_credentials_returns_token_pair(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: json_encode([
            'email'    => 'alice@example.com',
            'password' => 'secret123',
        ]));

        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $body);
        $this->assertArrayHasKey('refresh_token', $body);
        $this->assertEquals('Bearer', $body['token_type']);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: json_encode([
            'email'    => 'alice@example.com',
            'password' => 'wrongpassword',
        ]));

        $response = $controller($request);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid credentials', $body['error']);
    }

    public function test_login_with_unknown_email_returns_401(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: json_encode([
            'email'    => 'unknown@example.com',
            'password' => 'anypassword',
        ]));

        $response = $controller($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_login_with_missing_email_returns_422(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: json_encode([
            'password' => 'secret123',
        ]));

        $response = $controller($request);

        $this->assertEquals(422, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('fields', $body);
    }

    public function test_login_with_missing_password_returns_422(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: json_encode([
            'email' => 'alice@example.com',
        ]));

        $response = $controller($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_login_with_invalid_json_returns_422(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: 'not json at all');

        $response = $controller($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_login_returned_token_contains_correct_roles(): void
    {
        $controller = new LoginController($this->jwtService, $this->hasher, $this->userProvider);

        $request = Request::create('/api/auth/login', 'POST', content: json_encode([
            'email'    => 'alice@example.com',
            'password' => 'secret123',
        ]));

        $response = $controller($request);
        $body = json_decode($response->getContent(), true);

        $validated = $this->jwtService->validate($body['access_token']);
        $this->assertContains('ROLE_ADMIN', $validated->roles());
    }

    // -------------------------------------------------------------------------
    // REFRESH TOKEN CONTROLLER TESTS
    // -------------------------------------------------------------------------

    private function loginAndGetTokens(): array
    {
        $identity = new UserIdentity('user-alice', ['ROLE_USER', 'ROLE_ADMIN']);
        $token = $this->jwtService->issue($identity);
        return [
            'access_token'  => $token->accessToken,
            'refresh_token' => $token->refreshToken,
        ];
    }

    public function test_refresh_with_valid_token_returns_new_pair(): void
    {
        $controller = new RefreshTokenController($this->jwtService, $this->userProvider);
        $tokens = $this->loginAndGetTokens();

        $request = Request::create('/api/auth/refresh', 'POST', content: json_encode([
            'refresh_token' => $tokens['refresh_token'],
        ]));

        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $body);
        $this->assertNotEquals($tokens['refresh_token'], $body['refresh_token']);
    }

    public function test_refresh_old_token_is_revoked_after_use(): void
    {
        $controller = new RefreshTokenController($this->jwtService, $this->userProvider);
        $tokens = $this->loginAndGetTokens();

        // Use the refresh token once
        $request = Request::create('/api/auth/refresh', 'POST', content: json_encode([
            'refresh_token' => $tokens['refresh_token'],
        ]));
        $controller($request);

        // Try to use it again — should fail
        $response = $controller($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_refresh_with_expired_token_returns_401(): void
    {
        $expiredConfig = new JwtConfig(
            secret: 'test-secret-at-least-32-characters-long',
            accessTokenTtl: 900,
            refreshTokenTtl: -1,
            issuer: 'test',
        );
        $expiredService = new JwtService($expiredConfig, $this->tokenStorage);
        $identity = new UserIdentity('user-alice', []);
        $expiredToken = $expiredService->issue($identity);

        $controller = new RefreshTokenController($this->jwtService, $this->userProvider);

        $request = Request::create('/api/auth/refresh', 'POST', content: json_encode([
            'refresh_token' => $expiredToken->refreshToken,
        ]));

        $response = $controller($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_refresh_with_missing_token_returns_422(): void
    {
        $controller = new RefreshTokenController($this->jwtService, $this->userProvider);

        $request = Request::create('/api/auth/refresh', 'POST', content: json_encode([]));

        $response = $controller($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_refresh_with_access_token_instead_of_refresh_returns_401(): void
    {
        $controller = new RefreshTokenController($this->jwtService, $this->userProvider);
        $tokens = $this->loginAndGetTokens();

        $request = Request::create('/api/auth/refresh', 'POST', content: json_encode([
            'refresh_token' => $tokens['access_token'], // wrong token type
        ]));

        $response = $controller($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // LOGOUT CONTROLLER TESTS
    // -------------------------------------------------------------------------

    public function test_logout_revokes_all_refresh_tokens(): void
    {
        $identity = new UserIdentity('user-alice', ['ROLE_USER']);
        $token1 = $this->jwtService->issue($identity);
        $token2 = $this->jwtService->issue($identity);

        // Set identity in array adapter (simulates AuthMiddleware having run)
        $this->arrayAdapter->set('auth:identity', $identity);

        $currentUser = new CurrentUserProvider($this->arrayAdapter);
        $controller = new LogoutController($this->jwtService, $currentUser);

        $request = Request::create('/api/auth/logout', 'POST');
        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());

        // Both refresh tokens should now be revoked
        $refreshController = new RefreshTokenController($this->jwtService, $this->userProvider);

        $r1 = $refreshController(Request::create('/api/auth/refresh', 'POST', content: json_encode([
            'refresh_token' => $token1->refreshToken,
        ])));
        $this->assertEquals(401, $r1->getStatusCode());

        $r2 = $refreshController(Request::create('/api/auth/refresh', 'POST', content: json_encode([
            'refresh_token' => $token2->refreshToken,
        ])));
        $this->assertEquals(401, $r2->getStatusCode());
    }

    public function test_logout_returns_success_message(): void
    {
        $identity = new UserIdentity('user-alice', ['ROLE_USER']);
        $this->arrayAdapter->set('auth:identity', $identity);

        $currentUser = new CurrentUserProvider($this->arrayAdapter);
        $controller = new LogoutController($this->jwtService, $currentUser);

        $request = Request::create('/api/auth/logout', 'POST');
        $response = $controller($request);

        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertEquals('Logged out successfully', $body['message']);
    }
}
