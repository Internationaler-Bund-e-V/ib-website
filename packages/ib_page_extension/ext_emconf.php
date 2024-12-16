<?php

declare(strict_types=1);

// http://stackoverflow.com/questions/27682352/add-custom-page-configuration-field-in-typo3
$_EXTKEY = 'ib_page_extension';

$EM_CONF[$_EXTKEY] = array(
    'title' => 'IB Extent Page Settings',
    'description' => '',
    'category' => 'plugin',
    'author' => 'rms',
    'author_email' => 'mkettel@gmail.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '11.5.0-11.5.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
