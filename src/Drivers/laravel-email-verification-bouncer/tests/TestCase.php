<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationBouncer\Tests;

use Illuminate\Foundation\Application;
use Misaf\LaravelEmailVerification\Tests\TestCase as CoreTestCase;
use Misaf\LaravelEmailVerificationBouncer\Providers\BouncerServiceProvider;

abstract class TestCase extends CoreTestCase
{
    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            BouncerServiceProvider::class,
        ];
    }
}
