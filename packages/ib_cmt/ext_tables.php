<?php

declare(strict_types=1);

use IB\IbCmt\Controller\ContentController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(
    static function () {

        ExtensionUtility::registerModule(
            'IbCmt',
            'web', // Make module a submodule of 'web'
            'ibcmt', // Submodule key
            '', // Position
            [
                ContentController::class => 'list, listNews, listRedaktion,allow,snippets',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:ib_cmt/Resources/Public/Icons/user_mod_ibcmtbe.svg',
                'labels' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_ibcmtbe.xlf',
            ]
        );
    }
);
