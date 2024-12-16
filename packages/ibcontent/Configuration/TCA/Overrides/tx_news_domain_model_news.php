<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$fields = array(
    'subheadline' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:ibcontent/Resources/Private/Language/custom_news_fields.xlf:tx_news_domain_model_news.subheadline',
        'config' => array(
            'type' => 'input',
            'size' => 100,
        ),
    ),
);

ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', $fields);
ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'subheadline', '', 'after:title');
