<?php

declare(strict_types=1);

use Rms\IbGalerie\Middleware\GalleryReplacerMiddleware;

return [
    // 'backend => []
    'frontend' => [
        'ib_galerie' => [
            'target' => GalleryReplacerMiddleware::class,

            #'before' => [
            #    'another-middleware-identifier',
            #],
            #'after' => [
            #    GalleryReplacerMiddleware::class,
            #],
        ],
    ],
];
