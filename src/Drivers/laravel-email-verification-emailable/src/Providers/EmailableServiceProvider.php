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
        // Deferred, so this provider never resolves the manager itself. Doing
        // so during registration would build a throwaway manager whenever this
        // package is registered before the core one, silently losing the
        // driver.
        $this->callAfterResolving(
            EmailVerificationManager::class,
            function (EmailVerificationManager $manager): void {
                $manager->extend('emailable', fn(): EmailVerification => new EmailableEmailVerification(
                    Config::string('email-verification-emailable.host'),
                    Config::string('email-verification-emailable.api_key'),
                    Config::integer('email-verification-emailable.timeout.server', 5),
                    Config::integer('email-verification-emailable.timeout.client', 6),
                    Config::integer('email-verification-emailable.retry.times', 2),
                    Config::integer('email-verification-emailable.retry.sleep_milliseconds', 100),
                ));
            },
        );
    }
}
