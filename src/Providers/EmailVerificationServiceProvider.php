<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Providers;

use Illuminate\Contracts\Container\Container as Application;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class EmailVerificationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-email-verification')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/laravel-email-verification');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            EmailVerificationManager::class,
            fn(Application $app): EmailVerificationManager => new EmailVerificationManager($app),
        );

        $this->app->alias(EmailVerificationManager::class, 'email-verification');
    }

}
