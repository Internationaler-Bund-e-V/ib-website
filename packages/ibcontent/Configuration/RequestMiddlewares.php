<?php

declare(strict_types=1);

use Rms\Ibcontent\Middleware\RteMailtoReplacerMiddleware;
use Rms\Ibcontent\Middleware\RteTagReplacerMiddleware;

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
