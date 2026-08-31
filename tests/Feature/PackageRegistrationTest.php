<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Misaf\LaravelEmailVerification\Providers\EmailVerificationServiceProvider;

it('merges the package configuration without the application setting it first', function (): void {
    expect(config('email-verification.default'))->toBe('null')
        ->and(config('email-verification.allowed_domains'))->toBe([])
        ->and(config('laravel-email-verification'))->toBeNull();
});

it('registers the config file under the short-name publish tag', function (): void {
    $paths = ServiceProvider::pathsToPublish(EmailVerificationServiceProvider::class, 'email-verification-config');

    expect(array_keys($paths))->toHaveCount(1)
        ->and(array_keys($paths)[0])->toEndWith('config/email-verification.php')
        ->and(array_values($paths)[0])->toEndWith('config/email-verification.php');
});

it('registers the translations under the short-name publish tag', function (): void {
    $paths = ServiceProvider::pathsToPublish(EmailVerificationServiceProvider::class, 'email-verification-translations');

    expect(array_keys($paths))->toHaveCount(1)
        ->and(array_keys($paths)[0])->toEndWith('resources/lang')
        ->and(array_values($paths)[0])->toEndWith('lang/vendor/email-verification');
});

it('registers the install command under the short name', function (): void {
    expect(Artisan::all())->toHaveKey('email-verification:install');
});

it('publishes the config file when the install command runs', function (): void {
    $published = config_path('email-verification.php');

    expect(file_exists($published))->toBeFalse();

    $this->artisan('email-verification:install')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->assertSuccessful();

    expect(file_exists($published))->toBeTrue();
})->after(function (): void {
    @unlink(config_path('email-verification.php'));
});

it('resolves every failure message from the package translation namespace', function (string $key): void {
    expect(Lang::has("email-verification::validation.email.{$key}"))->toBeTrue()
        ->and(__("email-verification::validation.email.{$key}"))
        ->not->toBe("email-verification::validation.email.{$key}");
})->with(['domain_not_allowed', 'invalid', 'risky', 'undeliverable', 'unverifiable']);
