<?php

declare(strict_types=1);

$_EXTKEY = 'ib_formbuilder';

$EM_CONF[$_EXTKEY] = [
    'title' => 'IB Formbuilder',
    'description' => 'Framwork for building forms',
    'category' => 'plugin',
    'author' => 'Michael Kettel',
    'author_email' => 'mkettel@gmail.com',
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
