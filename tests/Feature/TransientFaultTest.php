<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Misaf\LaravelEmailVerification\Support\TransientFault;

function responseException(int $status): RequestException
{
    return new RequestException(new Response(new GuzzleHttp\Psr7\Response($status)));
}

it('retries a connection failure', function (): void {
    expect(TransientFault::shouldRetry(new ConnectionException('timeout')))->toBeTrue();
});

it('retries a server error', function (): void {
    expect(TransientFault::shouldRetry(responseException(500)))->toBeTrue();
});

it('does not retry a client error', function (): void {
    expect(TransientFault::shouldRetry(responseException(429)))->toBeFalse()
        ->and(TransientFault::shouldRetry(responseException(401)))->toBeFalse();
});

it('does not retry an unrelated exception', function (): void {
    expect(TransientFault::shouldRetry(new RuntimeException('boom')))->toBeFalse();
});
