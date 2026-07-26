<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class EmailValidationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-email-validation')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/laravel-email-validation');
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
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-validation'),
        ]);
    }
}
