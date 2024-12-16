<?php

declare(strict_types=1);

use Rms\IbGalerie\Controller\GalerieController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

$_EXTKEY = 'ib_galerie';
$extKey = 'ib_galerie';

call_user_func(
    static function ($extKey) {

        ExtensionUtility::registerModule(
            'IbGalerie',
            'web', // Make module a submodule of 'web'
            'ibgaleriebe', // Submodule key
            '', // Position
            [
                GalerieController::class => 'list, show, new, create, edit, update, delete',
            ],
            [
                'access' => 'user,group',
                'icon'   => 'EXT:' . $extKey . '/Resources/Public/Icons/ib_main_portal_logo.png',
                'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_ibgaleriebe.xlf',
            ]
        );

        ExtensionManagementUtility::addLLrefForTCAdescr('tx_ibgalerie_domain_model_galerie', 'EXT:ib_galerie/Resources/Private/Language/locallang_csh_tx_ibgalerie_domain_model_galerie.xlf');
        ExtensionManagementUtility::allowTableOnStandardPages('tx_ibgalerie_domain_model_galerie');
    },
    'ib_galerie'
);
