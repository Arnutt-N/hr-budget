<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * Create-or-fetch a disbursement record (session + activity).
 *
 * Idempotent at the service layer: an existing record for the same
 * (session_id, activity_id) is reused.
 */
final class CreateDisbursementRecordDto
{
    public function __construct(
        public readonly int $sessionId,
        public readonly int $activityId,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->sessionId <= 0) {
            $errors['session_id'] = 'กรุณาระบุ session ที่ถูกต้อง';
        }

        if ($this->activityId <= 0) {
            $errors['activity_id'] = 'กรุณาเลือกกิจกรรม';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = [];
        $body = file_get_contents('php://input');
        if ($body !== false && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        return new self(
            sessionId: (int) ($raw['session_id'] ?? 0),
            activityId: (int) ($raw['activity_id'] ?? 0),
        );
    }
}
