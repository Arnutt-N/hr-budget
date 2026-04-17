---
name: notifications
description: Guide for implementing Email, In-App, and Push notifications in the HR Budget project.
---

# Notifications Guide

Standards for implementing multi-channel notifications.

## 📑 Table of Contents

- [Notification Channels](#-notification-channels)
- [Email Notifications](#-email-notifications)
- [In-App Notifications](#-in-app-notifications)
- [Notification Templates](#-notification-templates)
- [Event-Driven Notifications](#-event-driven-notifications)

## 📬 Notification Channels

| Channel | Use Case | Implementation |
|:--------|:---------|:---------------|
| **Email** | การอนุมัติ, รายงาน | PHPMailer |
| **In-App** | แจ้งเตือนทันที | Database + JS polling |
| **Line Notify** | แจ้งเตือนด่วน (อนาคต) | Line API |

## ✉️ Email Notifications

### PHPMailer Configuration

```php
// src/Services/MailService.php
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private PHPMailer $mailer;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        
        // SMTP Configuration
        $this->mailer->isSMTP();
        $this->mailer->Host = env('MAIL_HOST');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = env('MAIL_USERNAME');
        $this->mailer->Password = env('MAIL_PASSWORD');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = env('MAIL_PORT', 587);
        
        // Thai language support
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->Encoding = 'base64';
        
        // Default sender
        $this->mailer->setFrom(
            env('MAIL_FROM_ADDRESS'),
            env('MAIL_FROM_NAME', 'HR Budget System')
        );
    }
    
    public function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            
            foreach ($attachments as $path => $name) {
                $this->mailer->addAttachment($path, $name);
            }
            
            $this->mailer->send();
            $this->mailer->clearAddresses();
            
            return true;
        } catch (\Exception $e) {
            Logger::error('Mail send failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
```

### Email Queue (Optional)

```php
// For high volume, queue emails
class EmailQueue
{
    public static function dispatch(string $to, string $template, array $data): int
    {
        return Database::insert('email_queue', [
            'to_email' => $to,
            'template' => $template,
            'data' => json_encode($data),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

// Cron job processes queue
// * * * * * php /var/www/hr_budget/scripts/process_email_queue.php
```

## 🔔 In-App Notifications

### Database Schema

```sql
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    read_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_unread (user_id, is_read, created_at)
);
```

### Notification Service

```php
class NotificationService
{
    public static function create(int $userId, string $type, string $title, ?string $message = null, ?string $link = null): int
    {
        return Database::insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public static function getUnread(int $userId, int $limit = 10): array
    {
        return Database::query(
            "SELECT * FROM notifications 
             WHERE user_id = ? AND is_read = FALSE 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    public static function markAsRead(int $notificationId): void
    {
        Database::update('notifications', $notificationId, [
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public static function getUnreadCount(int $userId): int
    {
        return Database::query(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        )->fetchColumn();
    }
}
```

### Frontend Integration

```javascript
// Notification polling
class NotificationManager {
    constructor() {
        this.pollInterval = 30000; // 30 seconds
        this.badge = document.getElementById('notification-badge');
        this.dropdown = document.getElementById('notification-dropdown');
    }

    init() {
        this.poll();
        setInterval(() => this.poll(), this.pollInterval);
        this.bindEvents();
    }

    async poll() {
        try {
            const response = await fetch('/api/notifications/unread');
            const data = await response.json();
            
            this.updateBadge(data.count);
            this.updateDropdown(data.notifications);
        } catch (error) {
            console.error('Notification poll failed:', error);
        }
    }

    updateBadge(count) {
        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.classList.remove('hidden');
        } else {
            this.badge.classList.add('hidden');
        }
    }

    updateDropdown(notifications) {
        this.dropdown.innerHTML = notifications.map(n => `
            <a href="${n.link || '#'}" class="notification-item ${n.is_read ? '' : 'unread'}" 
               data-id="${n.id}">
                <p class="font-medium">${n.title}</p>
                <p class="text-sm text-slate-500">${n.message || ''}</p>
                <p class="text-xs text-slate-400">${this.timeAgo(n.created_at)}</p>
            </a>
        `).join('') || '<p class="p-4 text-slate-500">ไม่มีการแจ้งเตือน</p>';
    }

    async markAsRead(notificationId) {
        await fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' });
        this.poll();
    }

    timeAgo(datetime) {
        // Implement relative time
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new NotificationManager().init();
});
```

## 📋 Notification Templates

### Template Structure

```php
// resources/views/emails/request_approved.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8fafc; }
        .button { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>คำขอได้รับการอนุมัติ</h1>
        </div>
        <div class="content">
            <p>เรียน <?= htmlspecialchars($data['user_name']) ?>,</p>
            <p>คำขอใช้งบประมาณหมายเลข <strong><?= $data['request_number'] ?></strong> ได้รับการอนุมัติแล้ว</p>
            <p>
                <strong>หัวข้อ:</strong> <?= htmlspecialchars($data['title']) ?><br>
                <strong>จำนวนเงิน:</strong> <?= number_format($data['amount'], 2) ?> บาท<br>
                <strong>อนุมัติโดย:</strong> <?= htmlspecialchars($data['approver_name']) ?>
            </p>
            <p style="text-align: center; margin-top: 20px;">
                <a href="<?= $data['link'] ?>" class="button">ดูรายละเอียด</a>
            </p>
        </div>
    </div>
</body>
</html>
```

### Notification Types

| Type | Email | In-App | Description |
|:-----|:-----:|:------:|:------------|
| `request_submitted` | ❌ | ✅ | คำขอถูกส่งแล้ว |
| `request_pending_approval` | ✅ | ✅ | มีคำขอรออนุมัติ |
| `request_approved` | ✅ | ✅ | คำขอได้รับอนุมัติ |
| `request_rejected` | ✅ | ✅ | คำขอถูกปฏิเสธ |
| `budget_exceeded` | ✅ | ✅ | งบประมาณเกินเพดาน |
| `deadline_reminder` | ✅ | ✅ | แจ้งเตือนกำหนดส่ง |

## 🎯 Event-Driven Notifications

### Observer Pattern

```php
class NotificationObserver
{
    public static function onRequestApproved(BudgetRequest $request): void
    {
        $user = User::find($request->requester_id);
        
        // In-App
        NotificationService::create(
            $user['id'],
            'request_approved',
            'คำขอได้รับการอนุมัติ',
            "คำขอ {$request->request_number} ได้รับการอนุมัติแล้ว",
            "/requests/{$request->id}"
        );
        
        // Email
        $mail = new MailService();
        $body = View::renderToString('emails/request_approved', [
            'user_name' => $user['name'],
            'request_number' => $request->request_number,
            'title' => $request->title,
            'amount' => $request->total_amount,
            'approver_name' => Auth::user()['name'],
            'link' => config('app.url') . "/requests/{$request->id}"
        ]);
        $mail->send($user['email'], 'คำขอได้รับการอนุมัติ', $body);
    }
}

// Usage in controller
Budget::approve($id);
NotificationObserver::onRequestApproved($request);
```
