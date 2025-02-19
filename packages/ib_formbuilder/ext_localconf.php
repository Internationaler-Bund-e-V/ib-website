<?php

declare(strict_types=1);

use Ib\IbFormbuilder\Controller\FormController;
use Ib\IbFormbuilder\Evaluation\MultipleEmailEvaluation;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

$_EXTKEY = 'ib_formbuilder';

call_user_func(
    static function ($extKey) {

        ExtensionUtility::configurePlugin(
            'IbFormbuilder',
            'Showform',
            [
                FormController::class => 'frontendShowForm,frontendFormAjaxSubmit',
            ],
            // non-cacheable actions
            [
                FormController::class => 'frontendShowForm,frontendFormAjaxSubmit',
            ]
        );

        // ------------------------------------------------------------------------------------------------
        // backend icons
        // ------------------------------------------------------------------------------------------------
        /** @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconRegistry->registerIcon(
            'tx-productcatalog-wizard_icon_generic',
            BitmapIconProvider::class,
            ['source' => 'EXT:' . $extKey . '/Resources/Public/Icons/tx_ibformbuilder_domain_model_form.gif']
        );

        //\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE: EXT:ib_formbuilder/Configuration/TypoScript/pageTSconfig.ts">');

        // wizards
        ExtensionManagementUtility::addPageTSConfig(
            'mod {
                wizards.newContentElement.wizardItems.plugins {
                    elements {
                        showform {
                            icon = ' . ExtensionManagementUtility::extPath($extKey) . 'Resources/Public/Icons/user_plugin_showform.svg
                            title = LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ib_formbuilder_domain_model_showform
                            description = LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ib_formbuilder_domain_model_showform.description
                            tt_content_defValues {
                                CType = list
                                list_type = ibformbuilder_showform
                            }
                        }
                    }
                    show = *
                }
            }'
        );
    },
    'ib_formbuilder'
);

// Register custom evaluator (can be used in TCA or flexform)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][MultipleEmailEvaluation::class] = '';
