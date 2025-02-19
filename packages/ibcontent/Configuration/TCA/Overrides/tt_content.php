<?php

declare(strict_types=1);

// StartPage Slider

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'ibcontent',
    'StartPageSlider',
    'IB Header Page Slider'
);
// Bubble Slider
ExtensionUtility::registerPlugin(
    'ibcontent',
    'BubbleSlider',
    'IB Bubble Page Slider'
);
// Jobs
ExtensionUtility::registerPlugin(
    'ibcontent',
    'Jobs',
    'Jobs Slider'
);
// Accordion
ExtensionUtility::registerPlugin(
    'ibcontent',
    'Accordion',
    'Accordion Module'
);
// text extended
ExtensionUtility::registerPlugin(
    'ibcontent',
    'TextExtended',
    'Text Extended'
);
// breadcrump
ExtensionUtility::registerPlugin(
    'ibcontent',
    'Breadcrump',
    'Breadcrump'
);
// sidebar map
ExtensionUtility::registerPlugin(
    'ibcontent',
    'SidebarMap',
    'Sidebar Map Module'
);
// sidebar downloads
ExtensionUtility::registerPlugin(
    'ibcontent',
    'SidebarDownloads',
    'Sidebar Downloads Module'
);
// MediaElement
ExtensionUtility::registerPlugin(
    'ibcontent',
    'MediaElement',
    'Media Element (Image/Video)'
);
// Content slider
ExtensionUtility::registerPlugin(
    'ibcontent',
    'ContentSlider',
    'Content Slider Module'
);
// Content contact form
ExtensionUtility::registerPlugin(
    'ibcontent',
    'ContactForm',
    'Kontaktformular'
);
// Content tiles
ExtensionUtility::registerPlugin(
    'ibcontent',
    'Tiles',
    'Kacheln'
);

/**
 * switchable controller update
 */
// DB Job Modul Show Job List
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBJobModulShowJobList',
    'DB Job Module - Show Joblist'
);
// DB Job Modul Show Job
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBJobModulShowJob',
    'DB Job Module - Show Job'
);
// DB Job Modul Show Foreign Job
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBJobModulShowForeignJob',
    'DB Job Module - Show Foreign Job'
);

// DB Product List Show Product
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBProductListShowProduct',
    'Show Product'
);
// DB Product List Show Location
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBProductListShowLocation',
    'Show Location'
);
// DB Product List Show Category
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBProductListShowCategory',
    'Show Category'
);
// DB Product List Show Product
ExtensionUtility::registerPlugin(
    'ibcontent',
    'DBProductListShowNews',
    'Show News'
);

/**
 * ******************************
 */
// Openstreetmap module
ExtensionUtility::registerPlugin(
    'ibcontent',
    'OSMMap',
    'Openstreetmap Map'
);
// Openstreetmap module
ExtensionUtility::registerPlugin(
    'ibcontent',
    'OSMList',
    'Openstreetmap List'
);
// social wall flocklr module
ExtensionUtility::registerPlugin(
    'ibcontent',
    'SocialWall',
    'Flockler'
);
// fundraisingbox
ExtensionUtility::registerPlugin(
    'ibcontent',
    'Fundraising',
    'Fundraising Box'
);

// raisenow
ExtensionUtility::registerPlugin(
    'ibcontent',
    'RaiseNow',
    'RaiseNow Widget'
);

$extensionName = GeneralUtility::underscoredToUpperCamelCase('ibcontent');

//
// Include flex forms
//
$pluginName = 'startpageslider';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_startpageslider.xml');

$pluginName = 'bubbleslider';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_bubbleslider.xml');

$pluginName = 'jobs';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_jobs.xml');

$pluginName = 'accordion';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_accordion.xml');

$pluginName = 'textextended';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_textextended.xml');

$pluginName = 'breadcrump';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_breadcrump.xml');

$pluginName = 'sidebarmap';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_sidebarmap.xml');

$pluginName = 'sidebardownloads';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_sidebardownloads.xml');

$pluginName = 'mediaelement';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_mediaelement.xml');

$pluginName = 'contentslider';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_contentslider.xml');

$pluginName = 'contactform';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_contactform.xml');

$pluginName = 'tiles';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_tiles.xml');

$pluginName = 'dbjobmodul';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_db_jobs.xml');

/**
 * switchable controller update
 */
$pluginName = 'dbjobmodulshowjoblist';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_db_jobs_showjoblist.xml');

$pluginName = 'dbjobmodulshowjob';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_db_jobs_showjob.xml');

$pluginName = 'dbproductlistshowcategory';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_productlist_category.xml');

$pluginName = 'dbproductlistshowlocation';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_productlist_location.xml');

$pluginName = 'dbproductlistshowproduct';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_productlist_product.xml');

/**
 * ******************************
 */

$pluginName = 'osmmap';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_openstreetmap.xml');

$pluginName = 'osmlist';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_openstreetmaplist.xml');

$pluginName = 'socialwall';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_socialwall.xml');

$pluginName = 'Fundraising';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_fundraising.xml');

// Remove obsolete soft reference key 'images', the references from RTE content to the original
// images are handled with the key 'rtehtmlarea_images'
// Set up soft reference index parsing for RTE images in pi_flexform
// see vendor/netresearch/rte-ckeditor-image/Configuration/TCA/Overrides/tt_content.php
// mk@rms, 2024-11-06
/*
$cleanSoftReferences = explode(
    ',',
    (string) $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['softref']
);

$cleanSoftReferences   = array_diff($cleanSoftReferences, ['images']);
$cleanSoftReferences[] = 'rtehtmlarea_images';

$GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['softref'] = implode(
    ',',
    $cleanSoftReferences
);
*/
$pluginName = 'raisenow';
$pluginSignature = strtolower($extensionName) . '_' . $pluginName;
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . 'ibcontent' . '/Configuration/FlexForms/content_raisenow.xml');
