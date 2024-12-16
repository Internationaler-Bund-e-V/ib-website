<?php

declare(strict_types=1);

$_EXTKEY = 'ib_dataprivacy';

$EM_CONF[$_EXTKEY] = [
    'title' => 'IB Dataprivacy',
    'description' => 'Datenschutz - Baukastensystem',
    'category' => 'plugin',
    'author' => 'rms',
    'author_company' => 'rms.',
    'author_email' => 'info@rm-solutions.de',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
    ],
];
