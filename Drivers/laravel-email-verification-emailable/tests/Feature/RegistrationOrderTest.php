<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerificationEmailable\EmailableEmailVerification;
use Misaf\LaravelEmailVerificationEmailable\Providers\EmailableServiceProvider;

it('registers the emailable driver when the core package is registered first', function (): void {
    expect(app(EmailVerificationManager::class)->driver('emailable'))
        ->toBeInstanceOf(EmailableEmailVerification::class);
});

it('merges the package configuration without the application setting it first', function (): void {
    expect(config('email-verification-emailable.timeout.server'))->toBe(5)
        ->and(config('email-verification-emailable.timeout.client'))->toBe(6)
        ->and(config('email-verification-emailable.retry.times'))->toBe(2)
        ->and(config('email-verification-emailable.retry.sleep_milliseconds'))->toBe(100)
        ->and(config('laravel-email-verification-emailable'))->toBeNull();
});

it('registers the config file under the short-name publish tag', function (): void {
    $paths = ServiceProvider::pathsToPublish(EmailableServiceProvider::class, 'email-verification-emailable-config');

    expect(array_keys($paths))->toHaveCount(1)
        ->and(array_keys($paths)[0])->toEndWith('config/email-verification-emailable.php')
        ->and(array_values($paths)[0])->toEndWith('config/email-verification-emailable.php');
});

it('registers the install command under the short name', function (): void {
    expect(Artisan::all())->toHaveKey('email-verification-emailable:install');
});

it('publishes the config file when the install command runs', function (): void {
    $published = config_path('email-verification-emailable.php');

    expect(file_exists($published))->toBeFalse();

    $this->artisan('email-verification-emailable:install')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->assertSuccessful();

    expect(file_exists($published))->toBeTrue();
})->after(function (): void {
    @unlink(config_path('email-verification-emailable.php'));
});
