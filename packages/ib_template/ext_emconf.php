<?php

declare(strict_types=1);

$_EXTKEY = 'ib_template';

$EM_CONF[$_EXTKEY] = [
    'title' => 'IB_Template',
    'description' => 'IB- Template Extension',
    'category' => 'plugin',
    'author' => '',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '1.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
