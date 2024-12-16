<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Apache Solr for TYPO3 - File Indexing',
    'description' => 'Add indexing for FileAbstractionLayer based files in TYPO3 CMS.',
    'category' => 'misc',
    'state' => 'stable',
    'uploadfolder' => '0',
    'createDirs' => '',
    'author' => 'Steffen Ritter, Timo Hund, Markus Friedrich, Rafael Kähm',
    'author_email' => 'solr-eb-support@dkd.de',
    'author_company' => '[rs]websystems',
    'clearCacheOnLoad' => 1,
    'version' => '11.0.0',
    'constraints' =>
    [
        'depends' => [
            'typo3' => '11.5.14-11.5.99',
            'filemetadata' => '',
            'frontend' => '',
            'scheduler' => '',
            'solr' => '11.5.0-11.6.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'tika' => '11.0.0-',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'ApacheSolrForTypo3\\Solrfal\\' => 'Classes',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'ApacheSolrForTypo3\\Solrfal\\Tests\\' => 'Tests',
        ],
    ],
];
