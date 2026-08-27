<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Tests\TestCase;

pest()->extend(TestCase::class)->in(
    'Feature',
    '../src/Drivers/*/tests/Feature',
);
