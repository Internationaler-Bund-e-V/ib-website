<?php

declare(strict_types=1);

use Rms\IbGalerie\Controller\GalerieController;

return [
    'ib_galerie' => [
        'parent' => 'web',
        'access' => 'user',
        'labels' => 'LLL:EXT:ib_galerie/Resources/Private/Language/locallang_ibgaleriebe.xlf',
        'iconIdentifier' => 'module-ib-galerie',
        'extensionName' => 'IbGalerie',
        'controllerActions' => [
            GalerieController::class => [
                'list',
            ],
        ],
    ],
];
