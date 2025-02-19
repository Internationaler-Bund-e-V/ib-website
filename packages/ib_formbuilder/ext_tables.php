<?php

declare(strict_types=1);

use Rms\IbFormbuilder\Controller\FormController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

$_EXTKEY = 'ib_formbuilder';
$extKey = 'ib_formbuilder';

call_user_func(
    static function ($extKey) {
        /*
        ExtensionUtility::registerModule(
            'IbFormbuilder',
            'web', // Make module a submodule of 'web'
            'ibforms', // Submodule key
            '', // Position
            [
                FormController::class => 'list, show, new, create, edit, update, delete, export, showEmailData, deleteEmailData, exportEmailDataAsCSV',
            ],
            [
                'access' => 'user,group',
                //'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/user_mod_ibforms.svg',
                'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/if_formbuilder_be_icon.png',
                'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_ibforms.xlf',
            ]
        );
        */

        ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_ibformbuilder_domain_model_form',
            'EXT:ib_formbuilder/Resources/Private/Language/locallang_csh_tx_ibformbuilder_domain_model_form.xlf'
        );

        ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_ibformbuilder_domain_model_emaildata',
            'EXT:ib_formbuilder/Resources/Private/Language/locallang_csh_tx_ibformbuilder_domain_model_emaildata.xlf'
        );
    },
    'ib_formbuilder'
);
