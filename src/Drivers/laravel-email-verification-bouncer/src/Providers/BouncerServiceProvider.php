<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationBouncer\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\LaravelEmailVerification\Contracts\EmailVerifier;
use Misaf\LaravelEmailVerification\EmailVerifierManager;
use Misaf\LaravelEmailVerificationBouncer\BouncerEmailVerifier;
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
        $this->app->make(EmailVerifierManager::class)->extend(
            'bouncer',
            fn(): EmailVerifier => new BouncerEmailVerifier(
                Config::string('laravel-email-verification-bouncer.host'),
                Config::string('laravel-email-verification-bouncer.api_key'),
            ),
        );
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Validation Bouncer', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-verification-bouncer'),
        ]);
    }
}
