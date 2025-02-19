<?php

declare(strict_types=1);

$_EXTKEY = 'ibsearch';

$EM_CONF[$_EXTKEY] = array(
    'title' => 'IB SEARCH',
    'description' => '',
    'category' => 'plugin',
    'author' => 'rms (mk)',
    'author_email' => 'mkettel@gmail.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '11.5.0-12.5.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
