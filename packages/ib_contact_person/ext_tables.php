<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}
$_EXTKEY = 'ib_contact_person';

/*
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Rms.' . $_EXTKEY,
    'Contactpersonib',
    'Show Contact Person'
);
*/

/**
 * Registers a Backend Module
 */
/*
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
'Rms.' . $_EXTKEY,
'web', // Make module a submodule of 'web'
'ibcontactperson', // Submodule key
'', // Position
array(
    'ContactPerson' => 'show',
),
array(
    'access' => 'user,group',
    'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
    'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_ibcontactperson.xlf',
    )
);
*/

ExtensionManagementUtility::addLLrefForTCAdescr('tx_ibcontactperson_domain_model_contactperson', 'EXT:ib_contact_person/Resources/Private/Language/locallang_csh_tx_ibcontactperson_domain_model_contactperson.xlf');
