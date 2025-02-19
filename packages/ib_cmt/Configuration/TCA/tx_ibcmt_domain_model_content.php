<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::allowTableOnStandardPages('tx_ibcmt_domain_model_content');

return [
    'ctrl' => [
        'title' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content',
        'label' => 'contentid',
        'iconfile' => 'EXT:ib_cmt/Resources/Public/Icons/tx_ibcmt_domain_model_content.gif',
        'sortby' => 'allowed',
    ],
    'columns' => [
        'contentid' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.contentid',
            'config' => [
                'type' => 'input',
                'size' => '20',
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'contentparentid' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.contentparentid',
            'config' => [
                'type' => 'input',
                'size' => '20',
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'contenttype' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.contenttype',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'readOnly' => 1,
                'items' => [
                    ['label' => 'Typo3', 'value' => 0],
                    ['label' => 'Redaktionstool', 'value' => 1],
                    ['label' => 'Typo3 News', 'value' => 2],
                ],
            ],
        ],
        'rtcontenttype' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.rtcontenttype',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'readOnly' => 1,
                'items' => [
                    ['label' => 'Standort', 'value' => 0],
                    ['label' => 'Angebot', 'value' => 1],
                    ['label' => 'Contentelement', 'value' => 2],
                ],
            ],
        ],
        'allowed' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.allowed',
            'config' => [
                'type' => 'check',
                'readOnly' => 1,
            ],

        ],
        'comment' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.comment',
            'config' => [
                'type' => 'text',
                'eval' => 'trim',
            ],
        ],
        'contenttstamp' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.contenttstamp',
            'config' => [
                'type' => 'datetime',
                'readOnly' => 1,
            ],
        ],
        'tstampallowed' => [
            'label' => 'LLL:EXT:ib_cmt/Resources/Private/Language/locallang_db.xlf:tx_ibcmt_domain_model_content.tstampallowed',
            'config' => [
                'type' => 'datetime',
                'readOnly' => 1,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'contentid, contentparentid, contenttype, rtcontenttype, allowed,contenttstamp, tstampallowed, comment'],
    ],
];
