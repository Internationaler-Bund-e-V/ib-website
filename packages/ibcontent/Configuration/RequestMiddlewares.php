<?php

declare(strict_types=1);

use Ib\Ibcontent\Middleware\RteMailtoReplacerMiddleware;
use Ib\Ibcontent\Middleware\RteTagReplacerMiddleware;

return [
    // 'backend => []
    'frontend' => [
        RteTagReplacerMiddleware::class => [
            'target' => RteTagReplacerMiddleware::class,
        ],
        RteMailtoReplacerMiddleware::class => [
            'target' => RteMailtoReplacerMiddleware::class,
        ],
    ],
];
