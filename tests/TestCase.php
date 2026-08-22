<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Tests;

use Illuminate\Foundation\Application;
use Misaf\LaravelEmailValidation\Providers\EmailValidationServiceProvider;
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
        ];
    }
}
