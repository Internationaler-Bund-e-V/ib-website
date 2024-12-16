<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::registerPageTSConfigFile(
    'bv_bbe',
    'Configuration/TSconfig/tsConfig.tsconfig',
    'Bundesverband BBE - TS Config'
);
