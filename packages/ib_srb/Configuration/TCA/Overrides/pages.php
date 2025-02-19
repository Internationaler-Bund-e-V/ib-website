<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::registerPageTSConfigFile(
    'ib_srb',
    'Configuration/TSconfig/tsConfig.tsconfig',
    'IB SRB - TS Config',
);
