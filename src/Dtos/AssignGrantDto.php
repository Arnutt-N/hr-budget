<?php
declare(strict_types=1);

namespace App\Dtos;

final class AssignGrantDto
{
    public const SCOPE_TYPES = ['organization', 'all', 'category', 'region'];

    public function __construct(
        public readonly int $roleId,
        public readonly string $scopeType,
        public readonly ?int $scopeRefId = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];
        if ($this->roleId <= 0) {
            $errors['role_id'] = 'กรุณาระบุบทบาท';
        }
        if (!in_array($this->scopeType, self::SCOPE_TYPES, true)) {
            $errors['scope_type'] = 'ประเภทขอบเขตไม่ถูกต้อง';
        } elseif ($this->scopeType === 'all') {
            if ($this->scopeRefId !== null) {
                $errors['scope_ref_id'] = 'ขอบเขต "ทั้งหมด" ต้องไม่ระบุหน่วยงาน/อ้างอิง';
            }
        } elseif ($this->scopeRefId === null || $this->scopeRefId <= 0) {
            $errors['scope_ref_id'] = 'กรุณาระบุค่าอ้างอิงของขอบเขต';
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
        $ref = $raw['scope_ref_id'] ?? null;
        return new self(
            roleId: (int) ($raw['role_id'] ?? 0),
            scopeType: (string) ($raw['scope_type'] ?? 'organization'),
            scopeRefId: ($ref === null || $ref === '') ? null : (int) $ref,
        );
    }
}
