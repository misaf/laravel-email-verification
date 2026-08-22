<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidationBouncer\Tests;

use Illuminate\Foundation\Application;
use Misaf\LaravelEmailValidation\Providers\EmailValidationServiceProvider;
use Misaf\LaravelEmailValidationBouncer\Providers\BouncerServiceProvider;
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
            EmailValidationServiceProvider::class,
            BouncerServiceProvider::class,
        ];
    }
}
