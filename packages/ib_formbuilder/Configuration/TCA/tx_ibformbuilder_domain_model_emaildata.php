<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_emaildata',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
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
        'searchFields' => 'form_name,emaildata_html,emaildata_csv,related_form_id,error_on_send',
        'iconfile' => 'EXT:ib_formbuilder/Resources/Public/Icons/tx_ibformbuilder_domain_model_form.gif',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, form_name, emaildata_html, emaildata_csv, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
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
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_ibformbuilder_domain_model_emaildata',
                'foreign_table_where' => 'AND tx_ibformbuilder_domain_model_emaildata.pid=###CURRENT_PID### AND tx_ibformbuilder_domain_model_emaildata.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
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
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled',
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
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
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
                'behaviour' => array(
                    'allowLanguageSynchronization' => 1,
                ),
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
        ],
        'form_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_emaildata.form_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'emaildata_html' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_emaildata.emaildata_html',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
            ],
        ],
        'emaildata_csv' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ib_formbuilder/Resources/Private/Language/locallang_db.xlf:tx_ibformbuilder_domain_model_emaildata.emaildata_csv',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
            ],
        ],
        'related_form_id' => [
            'label' => 'related_form_id',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'error_on_send' => [
            'label' => 'errors during email send process',
            'config' => [
                'readOnly' => 1,
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'no errors',
                    ],
                ],
            ],
        ],
    ],
];
