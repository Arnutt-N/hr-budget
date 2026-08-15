<?php

declare(strict_types=1);

namespace App\Dtos\Concerns;

/**
 * ตัวช่วยกลางของ DTO ที่อ่านจาก JSON request body
 * (แก้ finding: helper เดิมถูกก๊อปข้าม DTO 5 ชุด)
 */
trait RequestHelpers
{
    /** @return array<string,mixed> */
    private static function jsonBody(): array
    {
        $body = file_get_contents('php://input');
        if ($body === false || $body === '') {
            return [];
        }
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private static function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $parts = explode('-', $date);
        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }
}
