<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationEmailable\Providers;

use Illuminate\Support\Facades\Config;
use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerificationEmailable\EmailableEmailVerification;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class EmailableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-email-verification-emailable')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/laravel-email-verification-emailable');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->make(EmailVerificationManager::class)->extend(
            'emailable',
            fn(): EmailVerification => new EmailableEmailVerification(
                Config::string('laravel-email-verification-emailable.host'),
                Config::string('laravel-email-verification-emailable.api_key'),
                Config::integer('laravel-email-verification.retry.times', 2),
                Config::integer('laravel-email-verification.retry.sleep_milliseconds', 100),
            ),
        );
    }
}
