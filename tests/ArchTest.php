<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

/*
 * The trailing separator matters: without it the expectation also matches the
 * sibling Misaf\LaravelEmailVerificationBouncer and ...Emailable namespaces.
 */
$core = 'Misaf\LaravelEmailVerification\\';

arch('the core remains provider neutral')
    ->expect($core)
    ->not->toUse([
        'Misaf\LaravelEmailVerificationBouncer',
        'Misaf\LaravelEmailVerificationEmailable',
    ]);

arch('the core does not know how providers communicate')
    ->expect($core)
    ->not->toUse('Illuminate\Http\Client');
