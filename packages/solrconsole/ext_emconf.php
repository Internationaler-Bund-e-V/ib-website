<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Apache Solr for TYPO3 - Console',
    'description' => 'Less monkey business, more time for the important stuff',
    'version' => '11.0.0',
    'state' => 'stable',
    'category' => 'plugin',
    'author' => 'Benni Mack, Timo Hund',
    'author_email' => 'solr-eb-support@dkd.de',
    'author_company' => 'dkd Internet Service GmbH',
    'module' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'solr' => '11.5.0-11.6.99',
            'typo3' => '11.5.0-11.5.99'
        ],
        'conflicts' => [],
        'suggests' => [
            'devlog' => ''
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'ApacheSolrForTypo3\\Solrconsole\\' => 'Classes/',
        ]
    ]
];
