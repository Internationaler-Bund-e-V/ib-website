<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile(
    'ib_cmt',
    'Configuration/TypoScript',
    'IB CMT',
);
