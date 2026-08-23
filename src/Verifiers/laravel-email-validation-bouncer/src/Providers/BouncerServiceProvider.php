<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidationBouncer\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidationBouncer\BouncerEmailVerifier;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class BouncerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-email-validation-bouncer')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/laravel-email-validation-bouncer');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->make(EmailVerifierManager::class)->extend(
            'bouncer',
            fn(): EmailVerifier => new BouncerEmailVerifier(
                Config::string('laravel-email-validation-bouncer.host', ''),
                Config::string('laravel-email-validation-bouncer.api_key', ''),
            ),
        );
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Validation Bouncer', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-validation-bouncer'),
        ]);
    }
}
