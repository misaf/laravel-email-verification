<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\LaravelEmailVerification\Contracts\EmailVerifier;
use Misaf\LaravelEmailVerification\EmailVerifierManager;
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
        $this->app->singleton(EmailVerifierManager::class);
        $this->app->bind(
            EmailVerifier::class,
            fn(): EmailVerifier => $this->app->make(EmailVerifierManager::class)->verifier(),
        );
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Validation', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-verification'),
        ]);
    }
}
