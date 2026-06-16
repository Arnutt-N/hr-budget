<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Real HTTP client over the curl extension. The single outbound-I/O boundary
 * for ThaID/DOPA — kept behind HttpClientInterface so the OAuth services are
 * unit-tested with a fake instead of live network.
 *
 * Security: TLS peer + host verification are always on; redirects are NOT
 * followed (a redirect from a token/userinfo endpoint is an error, not a hop).
 * Secrets in $opts (form/basic_auth) are never logged here.
 */
final class CurlHttpClient implements HttpClientInterface
{
    private const TIMEOUT_SECONDS = 15;

    public function __construct()
    {
        if (!\function_exists('curl_init')) {
            throw new \RuntimeException('ext-curl is required for the ThaID OAuth client');
        }
    }

    public function request(string $method, string $url, array $opts = []): HttpResponse
    {
        $ch = curl_init();

        $headers = $opts['headers'] ?? [];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => self::TIMEOUT_SECONDS,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (isset($opts['form']) && is_array($opts['form'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opts['form']));
            }
        }

        if (isset($opts['basic_auth']) && is_array($opts['basic_auth'])) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $opts['basic_auth'][0] . ':' . $opts['basic_auth'][1]);
        }

        if ($headers !== []) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $body   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno  = curl_errno($ch);
        curl_close($ch);

        if ($body === false || $errno !== 0) {
            // Transport failure (DNS/TLS/timeout). Log the curl errno only — never
            // the URL query or request body, which may carry secrets.
            error_log("[http] curl_failed errno={$errno}");
            return new HttpResponse(0, '');
        }

        return new HttpResponse($status, (string) $body);
    }
}
