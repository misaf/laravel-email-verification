<?php

declare(strict_types=1);

arch('the bouncer driver depends on the core contract, not the other way around')
    ->expect('Misaf\LaravelEmailValidationBouncer')
    ->toUse('Misaf\LaravelEmailValidation\Contracts\EmailVerifier');
