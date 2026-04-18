<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Repositories\NotificationRepository;
use App\Services\NotificationService;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    private NotificationService $service;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($pdo);

        $pdo->exec("
            CREATE TABLE notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                link VARCHAR(255),
                is_read INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->service = new NotificationService();
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    public function testCreateNotificationReturnsId(): void
    {
        $id = $this->service->notify(1, 'approved', 'Test title', 'Test message', '/requests/1');
        $this->assertGreaterThan(0, $id);
    }

    public function testListByUserReturnsOnlyOwnNotifications(): void
    {
        $this->service->notify(1, 'approved', 'For user 1');
        $this->service->notify(2, 'rejected', 'For user 2');
        $this->service->notify(1, 'approved', 'Also for user 1');

        $result = $this->service->list(1, new \App\Dtos\NotificationQueryDto(page: 1, perPage: 20));
        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['meta']['total']);
    }

    public function testUnreadCountReturnsOnlyUnread(): void
    {
        $this->service->notify(1, 'approved', 'A');
        $this->service->notify(1, 'rejected', 'B');
        $this->service->notify(1, 'approved', 'C');

        $this->assertEquals(3, $this->service->getUnreadCount(1));
    }

    public function testUnreadCountIsPerUser(): void
    {
        $this->service->notify(1, 'approved', 'A');
        $this->service->notify(2, 'approved', 'B');

        $this->assertEquals(1, $this->service->getUnreadCount(1));
        $this->assertEquals(1, $this->service->getUnreadCount(2));
    }

    public function testMarkReadSetsIsRead(): void
    {
        $id = $this->service->notify(1, 'approved', 'A');

        $ok = $this->service->markRead($id, 1);
        $this->assertTrue($ok);
        $this->assertEquals(0, $this->service->getUnreadCount(1));
    }

    public function testMarkReadWrongUserReturnsFalse(): void
    {
        $id = $this->service->notify(1, 'approved', 'A');

        $ok = $this->service->markRead($id, 2);
        $this->assertFalse($ok);
        $this->assertEquals(1, $this->service->getUnreadCount(1));
    }

    public function testMarkReadNonexistentReturnsFalse(): void
    {
        $ok = $this->service->markRead(999, 1);
        $this->assertFalse($ok);
    }

    public function testMarkAllRead(): void
    {
        $this->service->notify(1, 'approved', 'A');
        $this->service->notify(1, 'rejected', 'B');
        $this->service->notify(1, 'approved', 'C');

        $ok = $this->service->markAllRead(1);
        $this->assertTrue($ok);
        $this->assertEquals(0, $this->service->getUnreadCount(1));
    }

    public function testMarkAllReadDoesNotAffectOtherUser(): void
    {
        $this->service->notify(1, 'approved', 'A');
        $this->service->notify(2, 'approved', 'B');

        $this->service->markAllRead(1);
        $this->assertEquals(0, $this->service->getUnreadCount(1));
        $this->assertEquals(1, $this->service->getUnreadCount(2));
    }

    public function testListPagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->service->notify(1, 'approved', "Notif {$i}");
        }

        $result = $this->service->list(1, new \App\Dtos\NotificationQueryDto(page: 1, perPage: 2));
        $this->assertCount(2, $result['data']);
        $this->assertEquals(5, $result['meta']['total']);
        $this->assertEquals(3, $result['meta']['total_pages']);
    }
}
