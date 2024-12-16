<?php

// Prevent Script from beeing called directly
defined('TYPO3_MODE') || die();

/**********************************************
 *
 *  REGISTER Events in TCEmain
 *
 *  Note that data handler hooks must be registered
 *  here to ensure the right order, EXT:solr
 *  hooks must be executed first.
 *
 **********************************************/

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['solrfal'] =
    \ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['solrfal'] =
    \ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect::class;

$modulePrefix = 'extensions-solrfal-module';
$svgProvider = \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class;
$extIconPath = 'EXT:solrfal/Resources/Public/Images/Icons/';

/* @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */ // register all module icons with extensions-solr-module-modulename
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
    $modulePrefix . 'solrfalcontrolpanel',
    $svgProvider,
    ['source' => $extIconPath . 'module-solrfal.svg']
);
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('solr')) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'ApacheSolrForTypo3.solrfal',
        'searchbackend',
        'SolrfalControlPanel',
        'bottom',
        [
            \ApacheSolrForTypo3\Solrfal\Controller\Backend\SolrModule\SolrfalControlPanelModuleController::class => 'index, clearSitesFileIndexQueue, showError, resetLogErrors',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:solrfal/Resources/Public/Images/Icons/module-solrfal.svg',
            'labels' => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:solr.backend.solrfal.label',
            'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        ]
    );
}

/********************************************************************************
 * Hook into ReQueue process of "Index Documents" (previously Index Inspector)
 ********************************************************************************/
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessIndexQueueUpdateItem']['ReQueue_solrfal_item'] =
    \ApacheSolrForTypo3\Solrfal\Queue\RequeueItemHandler::class;
