<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidationEmailable\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidationEmailable\EmailableEmailVerifier;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class EmailableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-email-validation-emailable')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/laravel-email-validation-emailable');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->make(EmailVerifierManager::class)->extend(
            'emailable',
            fn(): EmailVerifier => new EmailableEmailVerifier(
                Config::string('laravel-email-validation-emailable.host'),
                Config::string('laravel-email-validation-emailable.api_key'),
            ),
        );
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Validation Emailable', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-validation-emailable'),
        ]);
    }
}
