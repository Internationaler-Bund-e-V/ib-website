<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::registerPageTSConfigFile(
    'ib_dataprivacy',
    'Configuration/TSconfig/tsConfig.tsconfig',
    'IB Dataprivacy - TS Config',
);
