<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\EmailVerificationManager;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('the core remains provider neutral')
    ->expect([
        'Misaf\LaravelEmailVerification\Contracts',
        'Misaf\LaravelEmailVerification\Enums',
        'Misaf\LaravelEmailVerification\Facades',
        'Misaf\LaravelEmailVerification\Providers',
        'Misaf\LaravelEmailVerification\Rules',
        'Misaf\LaravelEmailVerification\Drivers',
        EmailVerificationManager::class,
    ])
    ->not->toUse([
        'Misaf\LaravelEmailVerificationBouncer',
        'Misaf\LaravelEmailVerificationEmailable',
    ]);
