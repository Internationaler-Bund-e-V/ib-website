<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'Ibjobs',
    'Iblogajobs',
    'IB Loga Jobs'
);

$pluginName = 'iblogajobs';
$pluginSignature = strtolower('ibjobs') . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:ibjobs/Configuration/FlexForms/ibjobs_showjobs.xml');
