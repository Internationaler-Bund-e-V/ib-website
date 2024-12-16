<?php

declare(strict_types=1);

$_EXTKEY = 'ib_cmt';

$EM_CONF[$_EXTKEY] = [
    'title' => 'IB - CMT',
    'description' => 'CMT',
    'category' => 'plugin',
    'author' => 'John Doe',
    'author_company' => 'rms',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-11.5.99',
        ],
    ],
];
