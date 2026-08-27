<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Tests;

use Illuminate\Foundation\Application;
use Misaf\LaravelEmailVerification\Providers\EmailVerificationServiceProvider;
use Misaf\LaravelEmailVerificationBouncer\Providers\BouncerServiceProvider;
use Misaf\LaravelEmailVerificationEmailable\Providers\EmailableServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

abstract class TestCase extends TestbenchTestCase
{
    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EmailVerificationServiceProvider::class,
            BouncerServiceProvider::class,
            EmailableServiceProvider::class,
        ];
    }
}
