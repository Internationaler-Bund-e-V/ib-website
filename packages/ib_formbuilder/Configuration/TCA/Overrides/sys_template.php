<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile(
    'ib_formbuilder',
    'Configuration/TypoScript',
    'IB Formbuilder'
);

// add flexForms for configuring the plugin in the backend
$pluginSignature = str_replace('_', '', 'ib_formbuilder') . '_' . 'showform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:ib_formbuilder/Configuration/FlexForms/add_form.xml',
);
