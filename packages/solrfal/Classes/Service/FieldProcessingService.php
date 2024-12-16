<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ApacheSolrForTypo3\Solrfal\Service;

use ApacheSolrForTypo3\Solr\FieldProcessor\Service;
use ApacheSolrForTypo3\Solr\FrontendEnvironment;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solr\IndexQueue\AbstractIndexer;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FieldProcessingService
 */
class FieldProcessingService extends AbstractIndexer
{
    /**
     * @var FieldProcessingService
     */
    protected static $instance;

    /**
     * @param Document $document
     * @param ContextInterface $context
     */
    public static function processFieldInstructions(Document $document, ContextInterface $context)
    {
        $documents = [$document];

        /* @var TypoScriptConfiguration $solrConfiguration */
        // needs to respect the TS settings of the page the item is on, conditions apply
        $solrConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId($context->getSite()->getRootPageId());
        $fieldProcessingInstructions = $solrConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.fieldProcessingInstructions.',
            []
        );

        // same as in the FE indexer
        if (is_array($fieldProcessingInstructions)) {
            /** @var Service $service */
            $service = GeneralUtility::makeInstance(Service::class);
            $service->processDocuments(
                $documents,
                $fieldProcessingInstructions
            );
        }
    }

    /**
     * Adds fields to the document as defined in $indexingConfiguration
     *
     * @param ContextInterface $context
     * @param Document $document base document to add fields to
     * @param array $indexingConfiguration Indexing configuration / mapping
     * @param array $data The record Data
     * @return Document Modified document with added fields
     */
    public static function addTypoScriptFieldsToDocument(ContextInterface $context, Document $document, array $indexingConfiguration, array $data)
    {
        if (self::$instance == null) {
            self::$instance = GeneralUtility::makeInstance(__CLASS__);
            self::$instance->type = 'sys_file_metadata';
        }

        $backupTsFe = null;
        if (!empty($GLOBALS['TSFE'])) {
            $backupTsFe = $GLOBALS['TSFE'];
        }

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(Tsfe::class)->getTsfeByPageIdAndLanguageId(
            $context->getSite()->getRootPageId(),
            $context->getLanguage()
        );
        $document = self::$instance->addDocumentFieldsFromTyposcript($document, $indexingConfiguration, $data, $GLOBALS['TSFE']);

        $GLOBALS['TSFE'] = $backupTsFe;

        return $document;
    }
}
