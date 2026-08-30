<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Tests\TestCase;
use Misaf\LaravelEmailVerificationBouncer\Tests\ReversedOrderTestCase as BouncerReversedOrderTestCase;
use Misaf\LaravelEmailVerificationBouncer\Tests\TestCase as BouncerTestCase;
use Misaf\LaravelEmailVerificationEmailable\Tests\ReversedOrderTestCase as EmailableReversedOrderTestCase;
use Misaf\LaravelEmailVerificationEmailable\Tests\TestCase as EmailableTestCase;

pest()->extend(TestCase::class)->in('Feature');
pest()->extend(BouncerTestCase::class)->in('../Drivers/laravel-email-verification-bouncer/tests/Feature');
pest()->extend(EmailableTestCase::class)->in('../Drivers/laravel-email-verification-emailable/tests/Feature');

// Registration-order coverage boots the providers in the reverse order, so it
// needs its own base test case — and therefore its own directory.
pest()->extend(BouncerReversedOrderTestCase::class)->in('../Drivers/laravel-email-verification-bouncer/tests/Registration');
pest()->extend(EmailableReversedOrderTestCase::class)->in('../Drivers/laravel-email-verification-emailable/tests/Registration');
