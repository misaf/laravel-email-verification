<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationEmailable\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
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
        $this->app->make(EmailVerificationManager::class)->extend(
            'emailable',
            fn(): EmailVerification => new EmailableEmailVerification(
                Config::string('laravel-email-verification-emailable.host'),
                Config::string('laravel-email-verification-emailable.api_key'),
            ),
        );
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Laravel Email Verification Emailable', fn(): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/laravel-email-verification-emailable'),
        ]);
    }
}
