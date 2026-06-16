<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Tiny HTTP contract so outbound calls (e.g. the ThaID/DOPA OAuth endpoints)
 * can be mocked in unit tests without hitting the network.
 *
 * $opts keys (all optional):
 *   - headers:    list<string>            e.g. ['Accept: application/json']
 *   - form:       array<string,string>    urlencoded request body (POST)
 *   - basic_auth: array{0:string,1:string} [user, pass] → Authorization: Basic
 */
interface HttpClientInterface
{
    /**
     * @param 'GET'|'POST'         $method
     * @param array<string,mixed>  $opts
     */
    public function request(string $method, string $url, array $opts = []): HttpResponse;
}
