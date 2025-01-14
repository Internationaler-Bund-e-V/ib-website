<?php

// configure solr logging
// see https://www.schmutt.de/typo3-solr-logging/
// mk@rms, 2023-07-11
// $GLOBALS['TYPO3_CONF_VARS']['LOG']['ApacheSolrForTypo3']['Solr']['writerConfiguration'] = [
//    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
//        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
//            'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/solr.log'
//        ]
//    ]
//];
include_once(__DIR__.'/../../config/system/additional.php');