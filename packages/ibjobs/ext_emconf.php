<?php

declare(strict_types=1);

$_EXTKEY = 'ibjobs';

$EM_CONF[$_EXTKEY] = [
    'title' => 'IB LOGA Jobs',
    'description' => '',
    'category' => 'plugin',
    'author' => '',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
