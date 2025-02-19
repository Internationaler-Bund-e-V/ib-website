<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Apache Solr for TYPO3 - File Indexing',
    'description' => 'Add indexing for FileAbstractionLayer based files in TYPO3 CMS.',
    'category' => 'misc',
    'state' => 'stable',
    'author' => 'Rafael Kaehm, Markus Friedrich',
    'author_email' => 'info@dkd.de',
    'author_company' => 'dkd Internet Service GmbH',
    'version' => '12.0.2',
    'constraints' =>
    [
        'depends' => [
            'typo3' => '12.4.1-12.4.99',
            'filemetadata' => '',
            'frontend' => '',
            'scheduler' => '',
            'solr' => '12.0.4-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'tika' => '12.0.2-',
        ],
    ],
];
