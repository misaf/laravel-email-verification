<?php

declare(strict_types=1);

use Misaf\LaravelEmailValidation\Tests\TestCase;
use Misaf\LaravelEmailValidationBouncer\Tests\TestCase as BouncerTestCase;
use Misaf\LaravelEmailValidationEmailable\Tests\TestCase as EmailableTestCase;

uses(TestCase::class)->in(__DIR__);

uses(BouncerTestCase::class)->in(__DIR__ . '/../src/Verifiers/laravel-email-validation-bouncer/tests');

uses(EmailableTestCase::class)->in(__DIR__ . '/../src/Verifiers/laravel-email-validation-emailable/tests');
