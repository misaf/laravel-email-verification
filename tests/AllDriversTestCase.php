<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Tests;

use Illuminate\Foundation\Application;
use Misaf\LaravelEmailValidation\Providers\EmailValidationServiceProvider;
use Misaf\LaravelEmailValidationBouncer\Providers\BouncerServiceProvider;
use Misaf\LaravelEmailValidationEmailable\Providers\EmailableServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

/**
 * Boots the core package alongside every first-party driver, mirroring an
 * application that installed more than one of them. Only the monorepo can
 * exercise this combination; the split repos each see a single driver.
 */
abstract class AllDriversTestCase extends TestbenchTestCase
{
    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EmailValidationServiceProvider::class,
            BouncerServiceProvider::class,
            EmailableServiceProvider::class,
        ];
    }
}
