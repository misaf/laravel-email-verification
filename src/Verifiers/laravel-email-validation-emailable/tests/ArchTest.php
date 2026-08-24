<?php

declare(strict_types=1);

arch('the emailable driver depends on the core contract, not the other way around')
    ->expect('Misaf\LaravelEmailValidationEmailable')
    ->toUse('Misaf\LaravelEmailValidation\Contracts\EmailVerifier');
