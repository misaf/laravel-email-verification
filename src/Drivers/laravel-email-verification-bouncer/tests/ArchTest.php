<?php

declare(strict_types=1);

arch('the bouncer driver depends on the core contract, not the other way around')
    ->expect('Misaf\LaravelEmailVerificationBouncer')
    ->toUse('Misaf\LaravelEmailVerification\Contracts\EmailVerifier');
