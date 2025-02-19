<?php

declare(strict_types=1);

use Rms\IbDataprivacy\Controller\DataprivacyController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::configurePlugin(
    'IbDataprivacy',
    'Dataprivacy',
    [
        DataprivacyController::class => 'list',
    ],
    // non-cacheable actions
    [
        DataprivacyController::class => 'list',
    ],
);
