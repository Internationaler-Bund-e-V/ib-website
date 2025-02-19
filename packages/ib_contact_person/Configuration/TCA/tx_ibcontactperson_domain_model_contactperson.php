<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:ib_contact_person/Resources/Private/Language/locallang_db.xlf:tx_ibcontactperson_domain_model_contactperson',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'searchFields' => 'name,position,contact_info,image,',
        'iconfile' => 'EXT:ib_contact_person/Resources/Public/Icons/tx_ibcontactperson_domain_model_contactperson.gif',
    ),

    'types' => array(
        //'1' => array('showitem' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,--palette--;;1,name,position,contact_info,image,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime,endtime'),
        '1' => array('showitem' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,--palette--;;1,name,position,contact_info,image,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime,endtime'),
    ),
    'palettes' => array(
        '1' => array('showitem' => ''),
    ),
    'columns' => array(

        'sys_language_uid' => array(
            'exclude' => 1,
            /*
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0),
                ),
            ),
            */
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ),
        'l10n_parent' => array(
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [[]],
                'foreign_table' => 'tx_ibcontactperson_domain_model_contactperson',
                'foreign_table_where' => 'AND tx_ibcontactperson_domain_model_contactperson.pid=###CURRENT_PID### AND tx_ibcontactperson_domain_model_contactperson.sys_language_uid IN (-1,0)',
            ),
        ),
        'l10n_diffsource' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),

        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'starttime' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => array(
                'type' => 'datetime',
                'size' => 13,
                'checkbox' => 0,
                'default' => 0,
                'behaviour' => array(
                    'allowLanguageSynchronization' => 1,
                ),
                'range' => array(
                    'lower' => mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y')),
                ),
            ),
        ),
        'endtime' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => array(
                'type' => 'datetime',
                'size' => 13,
                'checkbox' => 0,
                'default' => 0,
                'behaviour' => array(
                    'allowLanguageSynchronization' => 1,
                ),
                'range' => array(
                    'lower' => mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y')),
                ),
            ),
        ),

        'name' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:ib_contact_person/Resources/Private/Language/locallang_db.xlf:tx_ibcontactperson_domain_model_contactperson.name',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ),
        ),
        'position' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:ib_contact_person/Resources/Private/Language/locallang_db.xlf:tx_ibcontactperson_domain_model_contactperson.position',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ),
        ),
        'contact_info' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:ib_contact_person/Resources/Private/Language/locallang_db.xlf:tx_ibcontactperson_domain_model_contactperson.contact_info',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'enableRichtext' => 1,
                'richtextConfiguration' => 'default',
            ],
        ),
        'image' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:ib_contact_person/Resources/Private/Language/locallang_db.xlf:tx_ibcontactperson_domain_model_contactperson.image',

            'config' => [
                ### !!! Watch out for fieldName different from columnName
                'type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                'appearance' => array(
                    'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                ),
                'overrideChildTca' => array(
                    'columns' => array(
                        'uid_local' => array(
                            'config' => array(
                                'appearance' => array(
                                    'elementBrowserAllowed' => 'gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg',
                                    'elementBrowserType' => 'file',
                                ),
                            ),
                        ),
                    ),
                    'types' => array(
                        '0' => array(
                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                        ),
                        '1' => array(
                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                        ),
                        '2' => array(
                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                        ),
                        '3' => array(
                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                        ),
                        '4' => array(
                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                        ),
                        '5' => array(
                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                        ),
                    ),
                ),
                'maxitems' => 1,
            ],
        ),
    ),
);
