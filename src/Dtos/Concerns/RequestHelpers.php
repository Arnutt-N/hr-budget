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

    /**
     * แยก field ที่ผู้ใช้ "ส่งมาเป็น null/''" = ตั้งใจล้างค่า (clear) ออกจาก "ไม่ส่ง" = ไม่แตะ
     * แก้ debt: เดิม toUpdateData() ใช้ array_filter(fn($v) => $v !== null) ทำให้ล้างค่า
     * กลับเป็น NULL ใน DB ไม่ได้เลย (null แปลว่าไม่แตะเสมอ)
     *
     * @param string[] $keys ชื่อ field ที่ล้างได้
     * @return string[] เฉพาะ field ที่ส่ง null/'' มา
     */
    protected static function parseClearedFields(array $raw, array $keys): array
    {
        $cleared = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $raw) && in_array($raw[$key], [null, ''], true)) {
                $cleared[] = $key;
            }
        }
        return $cleared;
    }

    /** คัดลอก DTO พร้อม mark field ที่จะล้างเป็น NULL ใน DB */
    public function withCleared(array $keys): static
    {
        $clone = clone $this;
        $clone->cleared = $keys;
        return $clone;
    }

    /**
     * เติม null ให้ field ที่ถูกล้าง หลังกรองค่า "ไม่แตะ" ออก
     * ใช้ใน toUpdateData() — ลำดับสำคัญ: กรองก่อน แล้วค่อย override เป็น null
     */
    protected function applyCleared(array $data): array
    {
        foreach ($this->cleared as $key) {
            $data[$key] = null;
        }
        return $data;
    }

    private array $cleared = [];
}
