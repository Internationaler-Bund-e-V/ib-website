<?php

declare(strict_types=1);

use Rms\IbFormbuilder\Controller\FormController;

return [
    'ib_formbuilder' => [
        'parent' => 'web',
        'access' => 'user',
        'labels' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_ibforms.xlf',
        'icon' => 'EXT:ib_formbuilder/Resources/Public/Icons/if_formbuilder_be_icon.png',
        'extensionName' => 'IbFormbuilder',
        'path' => '/module/page/ibformbuilder',
        'controllerActions' => [
            FormController::class => [
                'list',
                'show',
                'new',
                'create',
                'edit',
                'update',
                'delete',
                'export',
                'showEmailData',
                'deleteEmailData',
                'exportEmailDataAsCSV',
            ],
        ],
    ],
];
