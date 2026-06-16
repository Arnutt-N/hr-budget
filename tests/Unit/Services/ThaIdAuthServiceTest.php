<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Core\Http\HttpResponse;
use App\Services\ThaIdAuthService;
use App\Services\ThaIdConfig;
use App\Services\ThaIdProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeHttpClient;

/**
 * Orchestration over an in-memory SQLite users table: CSRF state validation
 * and the find-or-create / email-link-gate / inactive rules in resolveUser.
 */
final class ThaIdAuthServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        // Mirror the NOT-NULL constraints from hr_budget_only.sql so a missing
        // required column (e.g. password) fails loudly instead of vacuously.
        $this->pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            name TEXT NOT NULL,
            avatar TEXT,
            role TEXT DEFAULT 'viewer',
            is_active INTEGER DEFAULT 1,
            department TEXT,
            thaid_sub TEXT,
            last_login_at TEXT,
            created_at TEXT,
            updated_at TEXT
        )");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function service(FakeHttpClient $http): ThaIdAuthService
    {
        $cfg = new ThaIdConfig([
            'mock' => false, 'client_id' => 'CID', 'client_secret' => 'SEC',
            'redirect_uri' => 'https://app/cb', 'token_url' => 'https://idp/token',
            'userinfo_url' => 'https://idp/userinfo', 'pkce' => true,
        ]);
        return new ThaIdAuthService($cfg, new ThaIdProvider($http, $cfg));
    }

    /** token + userinfo responses for a successful round trip. */
    private function okHttp(string $sub, string $email = '', bool $verified = false): FakeHttpClient
    {
        $info = json_encode([
            'sub' => $sub, 'name' => 'ทดสอบ', 'email' => $email, 'email_verified' => $verified,
        ], JSON_UNESCAPED_UNICODE);

        return new FakeHttpClient([
            new HttpResponse(200, '{"access_token":"AT"}'),
            new HttpResponse(200, $info),
        ]);
    }

    private function seedUser(array $cols): int
    {
        $cols = array_merge(['email' => 'x@y.z', 'password' => 'h', 'name' => 'n'], $cols);
        $keys = implode(',', array_keys($cols));
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        $this->pdo->prepare("INSERT INTO users ($keys) VALUES ($ph)")->execute(array_values($cols));
        return (int) $this->pdo->lastInsertId();
    }

    private function countUsers(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function test_state_mismatch_throws_before_any_http_call(): void
    {
        $http = new FakeHttpClient([]); // would error if called
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('state_mismatch');
        $this->service($http)->completeLogin('code', 'returned', 'EXPECTED', null);
    }

    public function test_empty_returned_state_is_rejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service(new FakeHttpClient([]))->completeLogin('code', '', 'EXPECTED', null);
    }

    public function test_new_identity_creates_viewer_with_hashed_password(): void
    {
        $user = $this->service($this->okHttp('PID-NEW'))
            ->completeLogin('code', 'S', 'S', 'verifier');

        $this->assertSame('PID-NEW', $user['thaid_sub']);
        $this->assertSame('viewer', $user['role']);
        $this->assertSame('PID-NEW@thaid.local', $user['email']); // synthetic (no real email)
        // Password column is populated with a genuine (bcrypt) hash of a random
        // secret — not the raw value — so the ThaID-only account can never be
        // password-logged-in but still satisfies the NOT-NULL constraint.
        $this->assertNotEmpty($user['password']);
        $this->assertNotNull(password_get_info((string) $user['password'])['algo']);
        $this->assertFalse(password_verify('', (string) $user['password']));
        $this->assertSame(1, $this->countUsers());
    }

    public function test_returning_user_is_not_duplicated(): void
    {
        $id = $this->seedUser(['email' => 'old@moj.go.th', 'thaid_sub' => 'PID-EXIST', 'role' => 'editor']);

        $user = $this->service($this->okHttp('PID-EXIST'))
            ->completeLogin('code', 'S', 'S', null);

        $this->assertSame($id, (int) $user['id']);
        $this->assertSame('editor', $user['role']); // existing role preserved
        $this->assertSame(1, $this->countUsers());
    }

    public function test_verified_email_links_existing_account(): void
    {
        $id = $this->seedUser(['email' => 'link@moj.go.th', 'thaid_sub' => null]);

        $user = $this->service($this->okHttp('PID-LINK', 'link@moj.go.th', true))
            ->completeLogin('code', 'S', 'S', null);

        $this->assertSame($id, (int) $user['id']);
        $this->assertSame('PID-LINK', $user['thaid_sub']); // back-filled
        $this->assertSame(1, $this->countUsers());
    }

    public function test_unverified_email_does_not_link_and_creates_new_user(): void
    {
        $id = $this->seedUser(['email' => 'victim@moj.go.th', 'thaid_sub' => null]);

        $user = $this->service($this->okHttp('PID-ATTACK', 'victim@moj.go.th', false))
            ->completeLogin('code', 'S', 'S', null);

        $this->assertNotSame($id, (int) $user['id']);         // did NOT take over the victim
        $this->assertSame('PID-ATTACK', $user['thaid_sub']);
        $this->assertSame(2, $this->countUsers());            // a separate account was created

        // Original account untouched.
        $victim = Database::queryOne("SELECT thaid_sub FROM users WHERE id = ?", [$id]);
        $this->assertNull($victim['thaid_sub']);
    }

    public function test_inactive_user_is_refused(): void
    {
        $this->seedUser(['thaid_sub' => 'PID-OFF', 'is_active' => 0]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('inactive');
        $this->service($this->okHttp('PID-OFF'))->completeLogin('code', 'S', 'S', null);
    }
}
