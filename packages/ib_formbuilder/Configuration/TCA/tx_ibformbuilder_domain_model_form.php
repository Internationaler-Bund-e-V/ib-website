<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_form',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'name,formdata_json,receiver',
        'iconfile' => 'EXT:ib_formbuilder/Resources/Public/Icons/tx_ibformbuilder_domain_model_form.gif',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, formdata_json, receiver, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            /*
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                        ]
                    ],
                    'default' => 0,
                ],
                */
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'default' => 0,
                'foreign_table' => 'tx_ibformbuilder_domain_model_form',
                'foreign_table_where' => 'AND tx_ibformbuilder_domain_model_form.pid=###CURRENT_PID### AND tx_ibformbuilder_domain_model_form.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled',
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'default' => 0,
                'behaviour' => array(
                    'allowLanguageSynchronization' => 1,
                ),
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'default' => 0,
                'behaviour' => array(
                    'allowLanguageSynchronization' => 1,
                ),
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
        ],
        'name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_form.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'formdata_json' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_form.formdata_json',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
            ],
        ],
        'receiver' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_form.receiver',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
            ],
        ],
    ],
];
