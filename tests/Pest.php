<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Tests\TestCase;
use Misaf\LaravelEmailVerificationBouncer\Tests\TestCase as BouncerTestCase;
use Misaf\LaravelEmailVerificationEmailable\Tests\TestCase as EmailableTestCase;

pest()->extend(TestCase::class)->in('Feature');
pest()->extend(BouncerTestCase::class)->in('../src/Drivers/laravel-email-verification-bouncer/tests/Feature');
pest()->extend(EmailableTestCase::class)->in('../src/Drivers/laravel-email-verification-emailable/tests/Feature');
