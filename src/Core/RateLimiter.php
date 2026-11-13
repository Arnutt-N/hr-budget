<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File-backed fixed-window rate limiter using flock for atomic updates.
 * Zero external dependencies — safe for single-host Apache/PHP deployments.
 */
final class RateLimiter
{
    private const DEFAULT_DIR = 'hr_budget_rl';

    /**
     * Record a hit for $key and return true if within $maxAttempts in $windowSeconds.
     */
    public static function allow(string $key, int $maxAttempts = 10, int $windowSeconds = 60, ?string $dir = null): bool
    {
        $path = self::pathFor($key, $dir);
        $now = time();

        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            // Fail-open if temp dir is unwritable so auth doesn't hard-break.
            return true;
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                return true;
            }
            $raw = stream_get_contents($fp);
            $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

            $resetAt = is_array($data) ? (int) ($data['reset_at'] ?? 0) : 0;
            $count = is_array($data) ? (int) ($data['count'] ?? 0) : 0;

            if ($now >= $resetAt) {
                $resetAt = $now + $windowSeconds;
                $count = 1;
            } else {
                $count++;
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string) json_encode(['count' => $count, 'reset_at' => $resetAt]));
            fflush($fp);
            flock($fp, LOCK_UN);

            return $count <= $maxAttempts;
        } finally {
            fclose($fp);
        }
    }

    public static function clear(string $key, ?string $dir = null): void
    {
        $path = self::pathFor($key, $dir);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function pathFor(string $key, ?string $dir = null): string
    {
        $base = $dir ?? (sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::DEFAULT_DIR);
        if (!is_dir($base)) {
            @mkdir($base, 0700, true);
        }
        return $base . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    }
}
