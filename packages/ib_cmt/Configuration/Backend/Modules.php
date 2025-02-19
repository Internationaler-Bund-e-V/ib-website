<?php

declare(strict_types=1);

use IB\IbCmt\Controller\ContentController;

return [
    'ib_cmt' => [
        'parent' => 'web',
        'access' => 'user',
        'labels' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_ibcmtbe.xlf',
        'icon' => 'EXT:ib_cmt/Resources/Public/Icons/Extension.svg',
        'extensionName' => 'IbCmt',
        'controllerActions' => [
            ContentController::class => [
                'list',
                'listNews',
                'listRedaktion',
                'snippets',
                'allow',
            ],
        ],
    ],
];
