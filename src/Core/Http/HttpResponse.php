<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Minimal HTTP response value object returned by HttpClientInterface.
 *
 * status === 0 signals a transport-level failure (DNS/TLS/timeout) — callers
 * treat any non-2xx (including 0) as an error.
 */
final class HttpResponse
{
    public function __construct(
        public readonly int $status,
        public readonly string $body,
    ) {}

    public function isOk(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /** @return array<string,mixed> Decoded JSON body, or [] when not decodable. */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);
        return is_array($decoded) ? $decoded : [];
    }
}
