<?php

declare(strict_types=1);

namespace App\Dtos;

final class ApprovalActionDto
{
    public function __construct(
        public readonly ?string $note = null,
    ) {}

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
            note: array_key_exists('note', $raw) ? trim((string) $raw['note']) : null,
        );
    }

    /**
     * @param 'approve'|'reject' $action
     * @return array<string,string>
     */
    public function validate(string $action): array
    {
        $errors = [];

        if ($action === 'reject' && ($this->note === null || $this->note === '')) {
            $errors['note'] = 'กรุณาระบุเหตุผลการปฏิเสธ';
        }

        return $errors;
    }
}
