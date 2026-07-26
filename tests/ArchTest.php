<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('the core never depends on a concrete verification provider')
    ->expect('Misaf\LaravelEmailValidation')
    ->not->toUse([
        'Misaf\LaravelEmailValidationEmailable',
    ]);
