<?php

declare(strict_types=1);

$_EXTKEY = 'ib_srb';

$EM_CONF[$_EXTKEY] = [
    'title' => 'Schwarz Rot Bunt Extension',
    'description' => 'Template & Contentelemente - Schwarz Rot Bunt',
    'category' => 'plugin',
    'author' => 'rms',
    'author_company' => 'rms',
    'author_email' => 'marco.schmidt@rm-solutions.de',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '1.5.0-1.5.99',
        ],
    ],
];
