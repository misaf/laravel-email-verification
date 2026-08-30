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
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('misaf/laravel-email-verification-bouncer');
            });
    }

    public function packageRegistered(): void
    {
        // Deferred, so this provider never resolves the manager itself. Doing
        // so during registration would build a throwaway manager whenever this
        // package is registered before the core one, silently losing the
        // driver.
        $this->callAfterResolving(
            EmailVerificationManager::class,
            function (EmailVerificationManager $manager): void {
                $manager->extend('bouncer', fn(): EmailVerification => new BouncerEmailVerification(
                    Config::string('email-verification-bouncer.host'),
                    Config::string('email-verification-bouncer.api_key'),
                    Config::integer('email-verification-bouncer.timeout.server', 5),
                    Config::integer('email-verification-bouncer.timeout.client', 6),
                    Config::integer('email-verification-bouncer.retry.times', 2),
                    Config::integer('email-verification-bouncer.retry.sleep_milliseconds', 100),
                ));
            },
        );
    }
}
