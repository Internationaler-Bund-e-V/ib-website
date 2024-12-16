<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'IbDataprivacy',
    'Dataprivacy',
    'IB Dataprivacy - Tools',
    'EXT:ib_dataprivacy/Resources/Public/Icons/Extension.svg',
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['ibdataprivacy_dataprivacy'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
    'ibdataprivacy_dataprivacy',
    // Flexform configuration schema file
    'FILE:EXT:ib_dataprivacy/Configuration/FlexForms/Dataprivacy.xml',
);
