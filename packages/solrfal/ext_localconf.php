<?php

defined('TYPO3') or die('Access denied.');

// Register garbage collection
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessGarbageCollector']['fileGarbageCollector'] = \ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect::class;

// When it is more easy to instanciate a different queue instance this can be replaced
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\ApacheSolrForTypo3\Solr\IndexQueue\Queue::class] = [
    'className' => \ApacheSolrForTypo3\Solrfal\Queue\Queue::class,
];

// adding scheduler tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\ApacheSolrForTypo3\Solrfal\Scheduler\IndexingTask::class] = [
    'extension'        => 'solrfal',
    'title'            => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.title',
    'description'      => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.description',
    'additionalFields' => \ApacheSolrForTypo3\Solrfal\Scheduler\IndexingTaskAdditionalFieldProvider::class,
];

if (isset($_SERVER['HTTP_X_TX_SOLR_IQ'])) {
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
