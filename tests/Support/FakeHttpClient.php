<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Http\HttpClientInterface;
use App\Core\Http\HttpResponse;

/**
 * Test double for HttpClientInterface: returns queued responses in order and
 * records every request so tests can assert on method/url/opts without any
 * real network I/O.
 */
final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<HttpResponse> */
    private array $queue;

    /** @var list<array{method:string,url:string,opts:array<string,mixed>}> */
    public array $requests = [];

    /** @param list<HttpResponse> $responses returned FIFO */
    public function __construct(array $responses)
    {
        $this->queue = $responses;
    }

    public function request(string $method, string $url, array $opts = []): HttpResponse
    {
        $this->requests[] = ['method' => $method, 'url' => $url, 'opts' => $opts];

        if ($this->queue === []) {
            return new HttpResponse(0, '');
        }

        return array_shift($this->queue);
    }
}
