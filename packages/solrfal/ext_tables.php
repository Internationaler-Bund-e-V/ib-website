<?php

// Prevent Script from being called directly
defined('TYPO3') || die();

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
