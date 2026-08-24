<?php

declare(strict_types=1);

use Misaf\LaravelEmailValidation\Tests\TestCase;

pest()->extend(TestCase::class)->in(
    'Feature',
    '../src/Verifiers/*/tests/Feature',
);
