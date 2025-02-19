<?php

declare(strict_types=1);

use Ib\IbContactPerson\Controller\ContactPersonController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$_EXTKEY = 'ib_contact_person';

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::configurePlugin(
    'ib_contact_person',
    'Contactpersonib',
    array(
        ContactPersonController::class => 'show',

    ),
    // non-cacheable actions
    array(
        ContactPersonController::class => '',
    )
);
