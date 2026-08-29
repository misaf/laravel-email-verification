<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Providers;

use Composer\InstalledVersions;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Console\AboutCommand;
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

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Verification', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-verification'),
        ]);
    }
}
