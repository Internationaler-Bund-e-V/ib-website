<?php

declare(strict_types=1);

//
// load static typoscript configuration
//
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile(
    'ibcontent',
    'Configuration/TypoScript/',
    'IB Content settings',
);
