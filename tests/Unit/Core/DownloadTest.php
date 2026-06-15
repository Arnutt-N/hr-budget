<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Download;
use PHPUnit\Framework\TestCase;

final class DownloadTest extends TestCase
{
    public function testSafeFilenameStripsCrlfAndQuotes(): void
    {
        $out = Download::safeFilename("a\r\nb\"c");

        $this->assertStringNotContainsString("\r", $out);
        $this->assertStringNotContainsString("\n", $out);
        $this->assertStringNotContainsString('"', $out);
    }

    public function testSafeFilenameStripsPathSegments(): void
    {
        $this->assertSame('passwd', Download::safeFilename('../../etc/passwd'));
    }

    public function testSafeFilenameEmptyFallsBackToDownload(): void
    {
        $this->assertSame('download', Download::safeFilename(''));
    }

    public function testSafeMimeTypeRejectsHeaderInjection(): void
    {
        $this->assertSame(
            'application/octet-stream',
            Download::safeMimeType("text/html\r\nSet-Cookie: x=1")
        );
    }

    public function testSafeMimeTypeAcceptsValidType(): void
    {
        $this->assertSame('application/pdf', Download::safeMimeType('application/pdf'));
    }

    public function testSafeMimeTypeNullFallsBack(): void
    {
        $this->assertSame('application/octet-stream', Download::safeMimeType(null));
    }

    public function testContentDispositionEncodesThaiName(): void
    {
        $name = 'งบประมาณ.pdf';
        $cd = Download::contentDisposition($name);

        $this->assertStringStartsWith('attachment;', $cd);
        $this->assertStringContainsString("filename*=UTF-8''" . rawurlencode($name), $cd);
        // ASCII-only quoted fallback is present and contains no raw quotes inside.
        $this->assertMatchesRegularExpression('/filename="[^"\r\n]*"/', $cd);
    }

    public function testContentDispositionAllNonAsciiFallsBackToDownload(): void
    {
        // A name with no ASCII chars must yield filename="download", not "____".
        $cd = Download::contentDisposition('งบประมาณ');

        $this->assertStringContainsString('filename="download"', $cd);
        $this->assertStringContainsString("filename*=UTF-8''" . rawurlencode('งบประมาณ'), $cd);
    }

    public function testSafeMimeTypeRejectsParameterizedType(): void
    {
        // Strict whitelist intentionally rejects parameter-bearing types.
        $this->assertSame(
            'application/octet-stream',
            Download::safeMimeType('text/html; charset=UTF-8')
        );
    }
}
