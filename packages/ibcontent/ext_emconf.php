<?php

declare(strict_types=1);

$_EXTKEY = 'ibcontent';

$EM_CONF[$_EXTKEY] = array(
    'title' => 'IB Content',
    'description' => '',
    'category' => 'plugin',
    'author' => 'RMS',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '11.5.0-12.5.99',
            'vhs' => '3.0.1',
            'ib_formbuilder' => '0.1.0-99.0.0',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
