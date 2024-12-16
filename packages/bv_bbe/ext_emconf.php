<?php

declare(strict_types=1);

$_EXTKEY = 'bv_bbe';

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bundesverband BBE Extension',
    'description' => 'Template & Contentelemente - Bundesverband Berufsbildungsexport',
    'category' => 'plugin',
    'author' => 'rms',
    'author_company' => 'rms',
    'author_email' => 'marco.schmidt@rm-solutions.de',
    'state' => 'alpha',
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
    ],
];
