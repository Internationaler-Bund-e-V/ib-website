<?php

declare(strict_types=1);

use Ib\Ibcontent\Controller\AjaxController;
use Ib\Ibcontent\Controller\ContactFormController;
use Ib\Ibcontent\Controller\MyContentController;
use Ib\Ibcontent\Hooks\NewsFlexFormHook;
use Ib\Ibcontent\Updates\PluginPermissionUpdaterDBJob;
use Ib\Ibcontent\Updates\PluginPermissionUpdaterDBProductList;
use Ib\Ibcontent\Updates\PluginUpdaterDBJob;
use Ib\Ibcontent\Updates\PluginUpdaterDBProductList;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\YouTubeHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$_EXTKEY = 'ibcontent';

//  start page slider
ExtensionUtility::configurePlugin(
    'ibcontent',
    'StartPageSlider',
    array(
        MyContentController::class => 'startPageSlider',
    ),
    // non-cacheable actions
    array(),
);

// bubble slider
ExtensionUtility::configurePlugin(
    'ibcontent',
    'BubbleSlider',
    array(
        MyContentController::class => 'bubbleSlider',
    ),
    // non-cacheable actions
    array(),
);

// jobs
ExtensionUtility::configurePlugin(
    'ibcontent',
    'Jobs',
    array(
        MyContentController::class => 'jobs',
    ),
    // non-cacheable actions
    array(),
);

// accordion
ExtensionUtility::configurePlugin(
    'ibcontent',
    'Accordion',
    array(
        MyContentController::class => 'accordion',
    ),
    // non-cacheable actions
    array()
);

// text extended
ExtensionUtility::configurePlugin(
    'ibcontent',
    'TextExtended',
    array(
        MyContentController::class => 'textextended',
    ),
    // non-cacheable actions
    array(),
);

// breadcrump
ExtensionUtility::configurePlugin(
    'ibcontent',
    'Breadcrump',
    array(
        MyContentController::class => 'breadcrump',
    ),
    // non-cacheable actions
    array(),
);

// sidebar map
ExtensionUtility::configurePlugin(
    'ibcontent',
    'SidebarMap',
    array(
        MyContentController::class => 'sidebarMap',
    ),
    // non-cacheable actions
    array(),
);

// sidebar downloads
ExtensionUtility::configurePlugin(
    'ibcontent',
    'SidebarDownloads',
    array(
        MyContentController::class => 'sidebarDownloads',
    ),
    // non-cacheable actions
    array(),
);

// media element
ExtensionUtility::configurePlugin(
    'ibcontent',
    'MediaElement',
    array(
        MyContentController::class => 'mediaElement',
    ),
    // non-cacheable actions
    array(),
);

// content slider
ExtensionUtility::configurePlugin(
    'ibcontent',
    'ContentSlider',
    array(
        MyContentController::class => 'contentSlider',
    ),
    // non-cacheable actions
    array(),
);

// contact form
ExtensionUtility::configurePlugin(
    'ibcontent',
    'ContactForm',
    array(
        ContactFormController::class => 'contactForm,submitContactForm',
    ),
    // non-cacheable actions
    array(
        ContactFormController::class => 'contactForm,submitContactForm',
    ),
);

// tiles
ExtensionUtility::configurePlugin(
    'ibcontent',
    'Tiles',
    array(
        MyContentController::class => 'tiles',
    ),
    // non-cacheable actions
    array(),
);

// db DBAjaxCalls
ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBAjaxCalls',
    array(
        AjaxController::class => 'submitLocationContactForm',
    ),
    array(
        AjaxController::class => 'submitLocationContactForm',
    ),
);

/**
 * switchable controller update
 */
// db job module
ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBJobModulShowJobList',
    array(
        MyContentController::class => 'dbShowJoblist',
    ),
    array(
        MyContentController::class => 'dbShowJoblist',
    ),
);

ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBJobModulShowJob',
    array(
        MyContentController::class => 'dbShowJob',
    ),
    array(
        MyContentController::class => 'dbShowJob',
    ),
);

ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBJobModulShowForeignJob',
    array(
        MyContentController::class => 'dbShowForeignjob',
    ),
    array(
        MyContentController::class => 'dbShowForeignjob',
    ),
);

ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBProductListShowProduct',
    array(
        MyContentController::class => 'dbShowProduct',
    ),
    array(
        MyContentController::class => 'dbShowProduct',
    ),
);
ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBProductListShowLocation',
    array(
        MyContentController::class => 'dbShowLocation',
    ),
    array(
        MyContentController::class => 'dbShowLocation',
    ),
);
ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBProductListShowCategory',
    array(
        MyContentController::class => 'dbShowCategory',
    ),
    array(
        MyContentController::class => 'dbShowCategory',
    ),
);
ExtensionUtility::configurePlugin(
    'ibcontent',
    'DBProductListShowNews',
    array(
        MyContentController::class => 'dbShowNews',
    ),
    array(
        MyContentController::class => 'dbShowNews',
    ),
);

/**
 * ***********************
 */

// openstreetmap module
ExtensionUtility::configurePlugin(
    'ibcontent',
    'OSMMap',
    array(
        MyContentController::class => 'osmShowMap',
    ),
    array(
        MyContentController::class => 'osmShowMap',
    ),
);

// openstreetmap list module
ExtensionUtility::configurePlugin(
    'ibcontent',
    'OSMList',
    array(
        MyContentController::class => 'osmShowList',
    ),
    array(
        MyContentController::class => 'osmShowList',
    ),
);

// flocklr
ExtensionUtility::configurePlugin(
    'ibcontent',
    'SocialWall',
    array(
        MyContentController::class => 'swShow',
    ),
    array(
        //MyContentController::class => 'swShow',
    ),
);

// fundraisingbox
ExtensionUtility::configurePlugin(
    'ibcontent',
    'Fundraising',
    array(
        MyContentController::class => 'frShow',
    ),
    array(
        MyContentController::class => 'frShow',
    ),
);

// raisenow
ExtensionUtility::configurePlugin(
    'ibcontent',
    'RaiseNow',
    array(
        MyContentController::class => 'rnShow',
    ),
    array(
        MyContentController::class => 'rnShow',
    ),
);

// ------------------------------------------------------------------------------------------------
// backend icons
// ------------------------------------------------------------------------------------------------
/** @var IconRegistry $iconRegistry */
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'tx-ibcontent-icon',
    BitmapIconProvider::class,
    ['source' => 'EXT:' . 'ibcontent' . '/Resources/Public/Icons/ibcontent.png']
);

// include page tsconfig for backend wizards
ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE: EXT:ibcontent/Configuration/TypoScript/pageTSconfig.typoscript">');

// include backend previews
/*
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_startpageslider'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_bubbleslider'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_breadcrump'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_jobs'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_accordion'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_textextended'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_sidebarmap'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_sidebardownloads'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_mediaelement'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_contentslider'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_contactform'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_tiles'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ibcontent_dbproductlist'][]
    = 'EXT:ibcontent/Classes/Utility/BackendPreview.php:BackendPreview->renderPluginPreview';

*/

/**
 * set Global Log Level -> MA#1480
 */
$GLOBALS['TYPO3_CONF_VARS']['LOG']['TYPO3']['CMS']['writerConfiguration'][LogLevel::WARNING] = [];

// configure "add media by URL" -> youtube only -> MA#1800

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers'] = [
    'youtube' => YouTubeHelper::class,
];

//register news hook for MA#1894
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][FlexFormTools::class]['flexParsing']['']
    = NewsFlexFormHook::class;

//register RteTagReplacer hook for MA#2041
#$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
#    = \Ib\Ibcontent\Hooks\RteTagReplacerHook::class . '->contentPostProcAll';

// overwrite locallang of be_secure_pw
// see 0001996: Passwortschutz des Backends (https://mantis.rm-solutions.de/mantis/view.php?id=1996)
// mk@rms, 2021-06-16
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:be_secure_pw/Resources/Private/Language/locallang.xml'][] =
    'EXT:ibcontent/Resources/Private/Language/Overrides/de.be_secure_pw_locallang.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:be_secure_pw/Resources/Private/Language/locallang_reminder.xml'][] =
    'EXT:ibcontent/Resources/Private/Language/Overrides/de.be_secure_pw_locallang_reminder.xlf';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:setup/Resources/Private/Language/locallang.xlf'][] = 'EXT:ibcontent/Resources/Private/Language/Overrides/de.typo3_setup_locallang.xlf';

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = 'ibcontent';

/**
 * switchable controller updates
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['txIBContentPluginUpdaterJob'] = PluginUpdaterDBJob::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['txIBContentPluginUpdaterProductList'] = PluginUpdaterDBProductList::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['txIBContentPluginPermissionUpdaterDBJob'] = PluginPermissionUpdaterDBJob::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['txIBContentPluginPermissionUpdaterDBProductList'] = PluginPermissionUpdaterDBProductList::class;
/************************************************************** */
