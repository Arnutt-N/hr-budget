<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateUserDto;
use App\Dtos\UpdateUserDto;
use App\Services\UserService;

class UserServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                avatar TEXT DEFAULT NULL,
                role TEXT DEFAULT 'viewer',
                is_active INTEGER DEFAULT 1,
                department TEXT DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                last_login_at TEXT DEFAULT NULL
            )
        ");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    /** @test */
    public function create_user_hashes_password(): void
    {
        $service = new UserService();
        $dto = new CreateUserDto('test@example.com', 'secret123', 'Test User', role: 'editor');

        $id = $service->create('admin', $dto);
        $this->assertNotNull($id);

        $row = Database::queryOne("SELECT password FROM users WHERE id = ?", [$id]);
        $this->assertTrue(password_verify('secret123', $row['password']));
    }

    /** @test */
    public function create_user_non_admin_fails(): void
    {
        $service = new UserService();
        $dto = new CreateUserDto('test@example.com', 'secret123', 'Test User');

        $id = $service->create('viewer', $dto);
        $this->assertNull($id);
    }

    /** @test */
    public function create_user_duplicate_email_fails(): void
    {
        $service = new UserService();
        $dto = new CreateUserDto('test@example.com', 'secret123', 'Test User');

        $id1 = $service->create('admin', $dto);
        $this->assertNotNull($id1);

        $id2 = $service->create('admin', $dto);
        $this->assertNull($id2);
    }

    /** @test */
    public function update_user_hashes_new_password(): void
    {
        $service = new UserService();
        $createDto = new CreateUserDto('test@example.com', 'oldpass', 'Test User');
        $id = $service->create('admin', $createDto);

        $updateDto = new UpdateUserDto(password: 'newpass');
        $ok = $service->update('admin', $id, $updateDto);
        $this->assertTrue($ok);

        $row = Database::queryOne("SELECT password FROM users WHERE id = ?", [$id]);
        $this->assertTrue(password_verify('newpass', $row['password']));
        $this->assertFalse(password_verify('oldpass', $row['password']));
    }

    /** @test */
    public function update_non_admin_fails(): void
    {
        $service = new UserService();
        $createDto = new CreateUserDto('test@example.com', 'secret', 'Test');
        $id = $service->create('admin', $createDto);

        $updateDto = new UpdateUserDto(name: 'New Name');
        $ok = $service->update('viewer', $id, $updateDto);
        $this->assertFalse($ok);
    }

    /** @test */
    public function self_delete_fails(): void
    {
        $service = new UserService();
        $createDto = new CreateUserDto('admin@example.com', 'secret', 'Admin', role: 'admin');
        $id = $service->create('admin', $createDto);

        $ok = $service->delete('admin', $id, $id);
        $this->assertFalse($ok);
    }

    /** @test */
    public function delete_other_user_succeeds(): void
    {
        $service = new UserService();

        $adminDto = new CreateUserDto('admin@example.com', 'secret', 'Admin', role: 'admin');
        $adminId = $service->create('admin', $adminDto);

        $userDto = new CreateUserDto('user@example.com', 'secret', 'User');
        $userId = $service->create('admin', $userDto);

        $ok = $service->delete('admin', $adminId, $userId);
        $this->assertTrue($ok);
    }

    /** @test */
    public function find_by_id_excludes_password(): void
    {
        $service = new UserService();
        $createDto = new CreateUserDto('test@example.com', 'secret', 'Test');
        $id = $service->create('admin', $createDto);

        $user = $service->findById($id);
        $this->assertArrayNotHasKey('password', $user);
        $this->assertEquals('test@example.com', $user['email']);
    }
}
