<?php

// Prevent Script from beeing called directly
defined('TYPO3_MODE') || die();

// Register initializing of the Index Queue for Files
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessIndexQueueInitialization']['solrfal'] = \ApacheSolrForTypo3\Solrfal\Queue\InitializationAspect::class;

// Register garbage collection
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessGarbageCollector']['fileGarbageCollector'] = \ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect::class;

// When it is more easy to instanciate a different queue instance this can be replaced
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['ApacheSolrForTypo3\Solr\IndexQueue\Queue'] = [
    'className' => \ApacheSolrForTypo3\Solrfal\Queue\Queue::class,
];

/**********************************************
 *
 *  REGISTER Events in the FAL API
 *
 **********************************************/

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

/****************************************
 *
 *  REGISTER Events internally
 *
 ****************************************/

$signalSlotDispatcher->connect(
    \ApacheSolrForTypo3\Solrfal\Queue\ItemRepository::class,
    'beforeItemRemoved',
    \ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect::class,
    'removeSolrEntryForItem'
);

$signalSlotDispatcher->connect(
    \ApacheSolrForTypo3\Solrfal\Queue\ItemRepository::class,
    'beforeMultipleItemsRemoved',
    \ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect::class,
    'removeMultipleQueueItemsFromSolr'
);

$signalSlotDispatcher->connect(
    \ApacheSolrForTypo3\Solr\Domain\Index\IndexService::class,
    'afterIndexItem',
    \ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect::class,
    'resetSuccessfulFileUids'
);

// adding scheduler tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['ApacheSolrForTypo3\Solrfal\Scheduler\IndexingTask'] = [
    'extension'        => 'solrfal',
    'title'            => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.title',
    'description'      => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.description',
    'additionalFields' => \ApacheSolrForTypo3\Solrfal\Scheduler\IndexingTaskAdditionalFieldProvider::class,
];

if (TYPO3_MODE == 'FE' && isset($_SERVER['HTTP_X_TX_SOLR_IQ'])) {
    // register PageContext stuff
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceStorage::class,
        'preGeneratePublicUrl',
        \ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect::class,
        'registerGeneratedPublicUrl'
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['Indexer']['indexPagePostProcessPageDocument'][] = \ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit'][] = \ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect::class;
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['LOG']['ApacheSolrForTypo3']['Solrfal']['writerConfiguration'])) {
    if (\TYPO3\CMS\Core\Core\Environment::getContext()->isProduction()) {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::ERROR;
    } elseif (\TYPO3\CMS\Core\Core\Environment::getContext()->isDevelopment()) {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::DEBUG;
    } else {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::INFO;
    }

    $GLOBALS['TYPO3_CONF_VARS']['LOG']['ApacheSolrForTypo3']['Solrfal']['writerConfiguration'] = [
        $logLevel => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/solrfal.log',
            ],
        ],
    ];
}
