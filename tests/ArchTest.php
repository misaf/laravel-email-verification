<?php

declare(strict_types=1);

use Misaf\LaravelEmailValidation\EmailVerifierManager;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('the core remains provider neutral')
    ->expect([
        'Misaf\LaravelEmailValidation\Contracts',
        'Misaf\LaravelEmailValidation\Enums',
        'Misaf\LaravelEmailValidation\Facades',
        'Misaf\LaravelEmailValidation\Providers',
        'Misaf\LaravelEmailValidation\Rules',
        'Misaf\LaravelEmailValidation\Verifiers',
        EmailVerifierManager::class,
    ])
    ->not->toUse([
        'Misaf\LaravelEmailValidationBouncer',
        'Misaf\LaravelEmailValidationEmailable',
    ]);
