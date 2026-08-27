<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationEmailable\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\LaravelEmailVerification\Contracts\EmailVerifier;
use Misaf\LaravelEmailVerification\EmailVerifierManager;
use Misaf\LaravelEmailVerificationEmailable\EmailableEmailVerifier;
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
        $this->app->make(EmailVerifierManager::class)->extend(
            'emailable',
            fn(): EmailVerifier => new EmailableEmailVerifier(
                Config::string('laravel-email-verification-emailable.host'),
                Config::string('laravel-email-verification-emailable.api_key'),
            ),
        );
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Validation Emailable', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-verification-emailable'),
        ]);
    }
}
