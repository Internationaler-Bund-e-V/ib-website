<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

/*
 * ------------------------------------------------------------------------------------------------
 * extend pages table with custom field(s)
 * ------------------------------------------------------------------------------------------------
 * add custom field to pages table
 * for details see // see https://docs.typo3.org/typo3cms/TCAReference/ExtendingTca/Examples/Index.html
 * @author mkettel @ rms, 2016-06
 */

// Adding fields to pages TCA
$temporaryColumns = array(
    'show_page_title' => array(
        'exclude' => 1,
        'onChange' => 'reload',
        'label' => 'Show Page Title Bar?',
        "config" => array(
            "type" => "check",
        ),
    ),
    'hide_breadcrumb' => array(
        'exclude' => 1,
        'onChange' => 'reload',
        'label' => 'Hide breadcrumb navigation',
        "config" => array(
            "type" => "check",
        ),
    ),
    'page_title' => array(
        'displayCond' => 'FIELD:show_page_title:REQ:TRUE',
        'exclude' => 1,
        'label' => 'Enter Page Title (if empty: Default Page Title)',
        "config" => array(
            "type" => "input",
        ),
    ),
    'page_theme' => array(
        'exclude' => 1,
        'label' => 'Select Page Theme',
        "config" => array(
            "type" => "select",
            "renderType" => "selectSingle",
            "items" => array(
                array('label' => 'default (portal)', 'value' => 'ib-theme-portal'),
                array('label' => 'Kitas', 'value' => 'ib-theme-kitas'),
                array('label' => 'Modern Look', 'value' => 'ib-theme-portal modern-look'),
            ),
        ),
    ),
    'contact_person' => array(
        'exclude' => 1,
        'label' => 'Select a Contact Person',
        "config" => array(
            "type" => "select",
            "renderType" => "selectSingle",
            "default" => 0,
            "items" => array(
                array('label' => 'none', 'value' => 0),
            ),
            'foreign_table' => 'tx_ibcontactperson_domain_model_contactperson',
        ),
    ),
    'contact_person_bg' => array(
        'displayCond' => 'FIELD:contact_person:!=:0',
        'exclude' => 1,
        'label' => 'Select Contact Person Background Color',
        "config" => array(
            "type" => "select",
            "renderType" => "selectSingle",
            "items" => array(
                array('label' => 'white', 'value' => 'white'),
                array('label' => 'gray', 'value' => 'gray'),
            ),
        ),
    ),
    'menue_layout' => array(
        'exclude' => 1,
        'label' => 'Select (Sub-)Menu Type',
        "config" => array(
            "type" => "select",
            "renderType" => "selectSingle",
            "items" => array(
                array('label' => 'IB - Images and no subpoints', 'value' => 0),
                array('label' => 'Map', 'value' => 1),
                array('label' => 'Service - incl. sublevel', 'value' => 2),
                array('label' => 'Nur in mobiler Navigation anzeigen', 'value' => 3),
                array('label' => 'Exclude from Menu', 'value' => 99),
            ),
        ),
    ),
    'bubble_text' => array(
        'exclude' => 1,
        'label' => 'Bubble Option - text',
        "config" => array(
            "type" => "input",
        ),
    ),
    'bubble_link' => array(
        'exclude' => 1,
        'label' => 'Bubble Option - Page ID',
        "config" => array(
            'type' => 'select',
            'foreign_table' => 'pages',
            //'foreign_table_where' => ' AND pages.uid = ###SITEROOT###',

            'size' => 10,
            'renderType' => 'selectTree',
            'treeConfig' => array(
                'expandAll' => true,
                'parentField' => 'pid',
                // 'rootUid' => '###SITE:rootPageId###',
                'startingPoints' => '###SITE:rootPageId###',
                // 'maxLevels' => 5,
                'appearance' => array(
                    'showHeader' => true,
                ),
            ),
        ),
    ),
    'bubble_title_text' => array(
        'exclude' => 1,
        'label' => 'Bubble Option - title text (mouse hover)',
        "config" => array(
            "type" => "input",
        ),
    ),
    'hide_print_icon' => array(
        'exclude' => 1,
        'label' => 'HIDE the print icon in the sidebar navigation',
        "config" => array(
            "type" => "check",
        ),
    ),
);

ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $temporaryColumns
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'show_page_title'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'hide_breadcrumb'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'page_title'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'page_theme'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'contact_person'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'contact_person_bg'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'bubble_text'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'bubble_link'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'bubble_title_text'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'menue_layout'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'hide_print_icon'
);
