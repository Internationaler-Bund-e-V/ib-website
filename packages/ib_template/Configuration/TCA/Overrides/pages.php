<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

/*
ExtensionManagementUtility::registerPageTSConfigFile(
    'ib_template',
    'Configuration/TSconfig/Page/news.txt',
    'EXT:ib_template :: Include News Templates');
*/

ExtensionManagementUtility::registerPageTSConfigFile(
    'ib_template',
    'Configuration/TSconfig/Page/ts_config.typoscript',
    'EXT:ib_template :: Include TS Config'
);
