<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use App\Dtos\CreateFileDto;
use PHPUnit\Framework\TestCase;

class CreateFileDtoTest extends TestCase
{
    private function makeDto(
        int $size = 1024,
        string $extension = 'pdf',
        int $uploadError = UPLOAD_ERR_OK,
    ): CreateFileDto {
        return new CreateFileDto(
            originalName: 'test.' . $extension,
            tmpPath: '/tmp/test',
            mimeType: 'application/pdf',
            size: $size,
            extension: $extension,
            uploadError: $uploadError,
        );
    }

    public function testValidFileReturnsNoErrors(): void
    {
        $dto = $this->makeDto();
        $this->assertEmpty($dto->validate());
    }

    public function testFileTooLargeReturnsError(): void
    {
        $dto = $this->makeDto(size: 15 * 1024 * 1024);
        $errors = $dto->validate();
        $this->assertArrayHasKey('file', $errors);
        $this->assertStringContainsString('10 MB', $errors['file']);
    }

    public function testInvalidExtensionReturnsError(): void
    {
        $dto = $this->makeDto(extension: 'exe');
        $errors = $dto->validate();
        $this->assertArrayHasKey('file', $errors);
        $this->assertStringContainsString('ประเภทไฟล์ไม่รองรับ', $errors['file']);
    }

    public function testUploadErrorReturnsError(): void
    {
        $dto = $this->makeDto(uploadError: UPLOAD_ERR_PARTIAL);
        $errors = $dto->validate();
        $this->assertArrayHasKey('file', $errors);
        $this->assertStringContainsString('อัปโหลดไฟล์ไม่สำเร็จ', $errors['file']);
    }

    public function testAllAllowedTypes(): void
    {
        $dto = $this->makeDto();
        $expected = ['pdf', 'xlsx', 'xls', 'csv', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif'];
        $this->assertSame($expected, $dto->allowedTypes());
    }

    public function testExactMaxSizeIsAllowed(): void
    {
        $dto = $this->makeDto(size: 10 * 1024 * 1024);
        $this->assertEmpty($dto->validate());
    }

    public function testOneByteOverMaxIsRejected(): void
    {
        $dto = $this->makeDto(size: 10 * 1024 * 1024 + 1);
        $this->assertArrayHasKey('file', $dto->validate());
    }

    public function testNullExtensionIsRejected(): void
    {
        $dto = new CreateFileDto(
            originalName: 'noext',
            tmpPath: '/tmp/test',
            mimeType: 'text/plain',
            size: 100,
            extension: null,
            uploadError: UPLOAD_ERR_OK,
        );
        $this->assertArrayHasKey('file', $dto->validate());
    }
}
