<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationBouncer\Providers;

use Illuminate\Support\Facades\Config;
use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerificationBouncer\BouncerEmailVerification;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class BouncerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-email-verification-bouncer')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/laravel-email-verification-bouncer');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->make(EmailVerificationManager::class)->extend(
            'bouncer',
            fn(): EmailVerification => new BouncerEmailVerification(
                Config::string('laravel-email-verification-bouncer.host'),
                Config::string('laravel-email-verification-bouncer.api_key'),
                Config::integer('laravel-email-verification.retry.times', 2),
                Config::integer('laravel-email-verification.retry.sleep_milliseconds', 100),
            ),
        );
    }
}
