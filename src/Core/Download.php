<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Safe file-download helpers.
 *
 * Sanitizes user-controlled file metadata (name, MIME type) before it reaches
 * response headers, preventing HTTP header / MIME injection and MIME sniffing.
 * Mirrors and extends the already-hardened API controller
 * (src/Api/Controllers/FileController.php): adds RFC 5987 filename* for Thai
 * names and X-Content-Type-Options: nosniff.
 *
 * Pure methods return strings (unit-testable); only sendFile() touches output.
 */
final class Download
{
    /**
     * Strip CR/LF, double-quotes and any path segments from a download name.
     * Falls back to "download" when nothing usable remains.
     */
    public static function safeFilename(string $name): string
    {
        $clean = str_replace(["\r", "\n", '"'], '', basename($name));

        return $clean === '' ? 'download' : $clean;
    }

    /**
     * Validate a MIME type against a conservative token/token grammar.
     * Strips CR/LF and rejects anything with parameters or stray characters,
     * falling back to application/octet-stream.
     */
    public static function safeMimeType(?string $mime): string
    {
        if ($mime === null) {
            return 'application/octet-stream';
        }

        $clean = trim(str_replace(["\r", "\n"], '', $mime));

        if (preg_match('~^[A-Za-z0-9!#$&^_.+-]+/[A-Za-z0-9!#$&^_.+-]+$~', $clean) === 1) {
            return $clean;
        }

        return 'application/octet-stream';
    }

    /**
     * Build a safe Content-Disposition value: an ASCII-only quoted fallback
     * plus an RFC 5987 filename* so non-ASCII (Thai) names survive intact.
     */
    public static function contentDisposition(string $name): string
    {
        // Single sanitized source: safeFilename() strips path + CR/LF + quotes.
        $clean = self::safeFilename($name);
        $ascii = preg_replace('/[^\x20-\x7E]/', '_', $clean) ?? '';
        // trim '_' so an all-non-ASCII (e.g. all-Thai) name falls back cleanly.
        if (trim($ascii, '_') === '') {
            $ascii = 'download';
        }
        $encoded = rawurlencode($clean);

        return "attachment; filename=\"{$ascii}\"; filename*=UTF-8''{$encoded}";
    }

    /**
     * Stream a file to the client as a sanitized attachment, then exit.
     * Caller is responsible for existence / authorization / path containment.
     */
    public static function sendFile(string $absPath, string $name, ?string $mime): void
    {
        header('Content-Type: ' . self::safeMimeType($mime));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: ' . self::contentDisposition($name));
        header('Content-Length: ' . filesize($absPath));
        readfile($absPath);
        exit;
    }
}
