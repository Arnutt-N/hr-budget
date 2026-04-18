<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateFileDto
{
    private const ALLOWED_TYPES = ['pdf', 'xlsx', 'xls', 'csv', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif'];
    private const MAX_SIZE = 10 * 1024 * 1024;

    public function __construct(
        public readonly ?string $originalName = null,
        public readonly ?string $tmpPath = null,
        public readonly ?string $mimeType = null,
        public readonly int $size = 0,
        public readonly ?string $extension = null,
        public readonly int $uploadError = UPLOAD_ERR_OK,
    ) {}

    public function validate(): array
    {
        $errors = [];

        if ($this->uploadError !== UPLOAD_ERR_OK) {
            $errors['file'] = 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาลองใหม่';
            return $errors;
        }

        if ($this->size > self::MAX_SIZE) {
            $errors['file'] = 'ไฟล์ขนาดเกิน 10 MB';
        }

        if ($this->extension === null || !in_array(strtolower($this->extension), self::ALLOWED_TYPES, true)) {
            $errors['file'] = 'ประเภทไฟล์ไม่รองรับ (รองรับ: pdf, xlsx, xls, csv, doc, docx, png, jpg, jpeg, gif)';
        }

        return $errors;
    }

    public static function fromUpload(): self
    {
        $file = $_FILES['file'] ?? null;
        if ($file === null) {
            return new self(uploadError: UPLOAD_ERR_NO_FILE);
        }

        $originalName = $file['name'] ?? '';
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        return new self(
            originalName: $originalName,
            tmpPath: $file['tmp_name'] ?? null,
            mimeType: $file['type'] ?? null,
            size: (int) ($file['size'] ?? 0),
            extension: $extension !== '' ? strtolower($extension) : null,
            uploadError: (int) ($file['error'] ?? UPLOAD_ERR_OK),
        );
    }

    public function allowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }
}
