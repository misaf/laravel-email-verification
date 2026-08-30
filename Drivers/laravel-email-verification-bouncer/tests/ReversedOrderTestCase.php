<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationBouncer\Tests;

use Illuminate\Foundation\Application;
use Misaf\LaravelEmailVerification\Providers\EmailVerificationServiceProvider;
use Misaf\LaravelEmailVerificationBouncer\Providers\BouncerServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

/**
 * Registers this driver package before the core package, the order Laravel's
 * package discovery is free to pick. Nothing may depend on the core provider
 * having run first.
 */
abstract class ReversedOrderTestCase extends TestbenchTestCase
{
    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            BouncerServiceProvider::class,
            EmailVerificationServiceProvider::class,
        ];
    }
}
