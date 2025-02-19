<?php

declare(strict_types=1);

use Ib\IbFormbuilder\Domain\Model\Content;

return [
    Content::class => [
        'tableName' => 'tt_content',
        'properties' => [
            'uid' => [
                'fieldName' => 'uid',
            ],
            'flexform' => [
                'fieldName' => 'pi_flexform',
            ],
            //'isAbsolutePath' => [
            //    'fieldName' => 'base'
            //],
        ],
    ],
];
