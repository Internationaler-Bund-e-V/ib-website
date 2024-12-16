<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(
    static function () {
        ExtensionManagementUtility::addStaticFile('ib_template', 'Configuration/TypoScript', 'IB_Template');
    }
);
