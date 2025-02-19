<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Apache Solr for TYPO3 - Console',
    'description' => 'Less monkey business, more time for the important stuff',
    'version' => '12.0.1',
    'state' => 'stable',
    'category' => 'plugin',
    'author' => 'Benni Mack, Timo Hund',
    'author_email' => 'solr-eb-support@dkd.de',
    'author_company' => 'dkd Internet Service GmbH',
    'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'solr' => '12.0.3-12.9.99',
            'typo3' => '12.4.3-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'ApacheSolrForTypo3\\Solrconsole\\' => 'Classes/',
            'ApacheSolrForTypo3\\Solrconsole\\Tests\\' => 'Tests/',
        ],
    ],
];
