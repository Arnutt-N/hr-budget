<?php
/**
 * Base Test Case
 * Provides common testing utilities
 */

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    protected static $db;
    protected $currentUser;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we have a database connection
        if (!self::$db) {
            self::$db = Database::getPdo();
        }
        
        // Check if we are already in a transaction (unexpected state)
        if (self::$db->inTransaction()) {
            self::$db->rollBack();
        }
        
        // Start a fresh transaction for this test
        self::$db->beginTransaction();
        
        // Clear tables to ensure clean state (optional but safer for stats tests)
        // Note: For large DBs this is slow, but for unit tests it ensures isolation
        // Alternatively, we rely on rollback, but if stats counts are global, rollback is best.
        // We will stick to rollback, but for stats tests we must consider pre-existing data.
        
        Auth::logout();
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        // Rollback the transaction to revert all changes made during the test
        if (self::$db && self::$db->inTransaction()) {
            self::$db->rollBack();
        }
        
        parent::tearDown();
    }

    /**
     * Act as a specific user
     */
    protected function actingAs(array $user): self
    {
        Auth::login($user);
        $this->currentUser = $user;
        return $this;
    }

    /**
     * Create a test user
     */
    protected function createUser(array $overrides = []): array
    {
        $data = array_merge([
            'email' => 'test' . uniqid() . '@moj.go.th',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'role' => 'viewer',
            'department' => 'Test Department',
            'is_active' => true
        ], $overrides);

        $id = User::create($data);
        return User::find($id);
    }

    /**
     * Create admin user
     */
    protected function createAdmin(): array
    {
        return $this->createUser([
            'role' => 'admin',
            'name' => 'Admin User'
        ]);
    }

    /**
     * Create editor user
     */
    protected function createEditor(): array
    {
        return $this->createUser([
            'role' => 'editor',
            'name' => 'Editor User'
        ]);
    }

    /**
     * Assert that database has record
     */
    protected function assertDatabaseHas(string $table, array $data): void
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE ";
        $conditions = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $conditions[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $conditions);
        
        $result = Database::query($sql, $params);
        $count = $result[0]['count'] ?? 0;
        
        $this->assertGreaterThan(0, $count, "Failed asserting that table '{$table}' has matching record.");
    }

    /**
     * Assert that database does not have record
     */
    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE ";
        $conditions = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $conditions[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(' AND ', $conditions);
        
        $result = Database::query($sql, $params);
        $count = $result[0]['count'] ?? 0;
        
        $this->assertEquals(0, $count, "Failed asserting that table '{$table}' does not have matching record.");
    }

    /**
     * Simulate POST request (for integration tests)
     */
    protected function post(string $uri, array $data = []): array
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $data;
        
        // This is simplified - in real implementation you'd need to route and capture output
        return ['status' => 200, 'redirect' => null];
    }

    /**
     * Simulate GET request
     */
    protected function get(string $uri): array
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        return ['status' => 200];
    }
}
