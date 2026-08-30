<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerificationBouncer\BouncerEmailVerification;
use Misaf\LaravelEmailVerificationBouncer\Providers\BouncerServiceProvider;

it('registers the bouncer driver when the core package is registered first', function (): void {
    expect(app(EmailVerificationManager::class)->driver('bouncer'))
        ->toBeInstanceOf(BouncerEmailVerification::class);
});

it('merges the package configuration without the application setting it first', function (): void {
    expect(config('email-verification-bouncer.timeout.server'))->toBe(5)
        ->and(config('email-verification-bouncer.timeout.client'))->toBe(6)
        ->and(config('email-verification-bouncer.retry.times'))->toBe(2)
        ->and(config('email-verification-bouncer.retry.sleep_milliseconds'))->toBe(100)
        ->and(config('laravel-email-verification-bouncer'))->toBeNull();
});

it('registers the config file under the short-name publish tag', function (): void {
    $paths = ServiceProvider::pathsToPublish(BouncerServiceProvider::class, 'email-verification-bouncer-config');

    expect(array_keys($paths))->toHaveCount(1)
        ->and(array_keys($paths)[0])->toEndWith('config/email-verification-bouncer.php')
        ->and(array_values($paths)[0])->toEndWith('config/email-verification-bouncer.php');
});

it('registers the install command under the short name', function (): void {
    expect(Artisan::all())->toHaveKey('email-verification-bouncer:install');
});
