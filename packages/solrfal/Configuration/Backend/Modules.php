<?php

/**
 * Definitions for modules provided by EXT:solr
 */

use ApacheSolrForTypo3\Solrfal\Controller\Backend\SolrModule\SolrfalControlPanelModuleController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$enabledModules = [];

if (ExtensionManagementUtility::isLoaded('solr')) {
    $enabledModules['solrfal'] = [
        'parent' => 'searchbackend',
        'access' => 'user,group',
        'path' => '/module/searchbackend/solrfal',
        'iconIdentifier' => 'extensions-solr-module-solrfalcontrolpanel',
        'labels' => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:solr.backend.solrfal.label',
        'extensionName' => 'Solrfal',
        'controllerActions' => [
            SolrfalControlPanelModuleController::class => [
                'index', 'clearSitesFileIndexQueue', 'showError', 'resetLogErrors',
            ],
        ],
    ];
}

return $enabledModules;
