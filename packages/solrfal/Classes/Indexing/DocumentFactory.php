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

namespace ApacheSolrForTypo3\Solrfal\Indexing;

use ApacheSolrForTypo3\Solr\Domain\Variants\IdBuilder;
use ApacheSolrForTypo3\Solr\FrontendEnvironment;
use ApacheSolrForTypo3\Solr\HtmlContentExtractor;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solr\Util;
use ApacheSolrForTypo3\Solrfal\Context\RecordContext;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;
use ApacheSolrForTypo3\Solrfal\Service\FieldProcessingService;
use ApacheSolrForTypo3\Solrfal\Service\FileUrlService;
use ApacheSolrForTypo3\Solrfal\System\Environment\FrontendServerEnvironment;
use ApacheSolrForTypo3\Solrfal\System\Language\OverlayService;
use ApacheSolrForTypo3\Tika\Service\Extractor\TextExtractor;
use ArrayObject;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Psr\Http\Client\ClientExceptionInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\TextExtraction\TextExtractorRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class DocumentFactory
 *
 * (c) 2013 Steffen Ritter <steffen.ritter@typo3.org>
 */
class DocumentFactory
{
    const SOLR_TYPE = 'tx_solr_file';

    /**
     * @var IdBuilder
     */
    protected $variantIdBuilder;

    /**
     * @var FrontendServerEnvironment
     */
    protected $frontendHttpHost;

    /**
     * DocumentFactory constructor.
     * @param IdBuilder|null $variantIdBuilder
     * @param FrontendServerEnvironment|null $frontendHttpHost
     */
    public function __construct(
        IdBuilder $variantIdBuilder = null,
        FrontendServerEnvironment $frontendHttpHost = null
    ) {
        $this->variantIdBuilder = $variantIdBuilder ?? GeneralUtility::makeInstance(IdBuilder::class);
        $this->frontendHttpHost = $frontendHttpHost ?? GeneralUtility::makeInstance(FrontendServerEnvironment::class);
    }

    /**
     * @param Item $item
     * @return Document
     * @throws AspectNotFoundException
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws FileDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws SiteNotFoundException
     */
    public function createDocumentForQueueItem(Item $item): Document
    {
        $this->initializeFrontendEnvironment($item);

        /* @var Document $document */
        $document = GeneralUtility::makeInstance(Document::class);

        $this->addFileInformation($document, $item);

        try {
            $this->addFileTextContent($document, $item);
        } catch (NoSuitableExtractorFoundException $e) {
            // no extractor found to extract text, we might want to index
            // the document without the text content but log an info message
            $this->getLogger()->info($e->getMessage());
        }
        $this->addContextInformation($document, $item);
        $this->addFieldsFromTypoScriptConfiguration($document, $item);

        FieldProcessingService::processFieldInstructions($document, $item->getContext());

        // only add endtime to solr document if it was set in referencing document
        // otherwise the Solr garbage collector will remove it (since Solr Server 4.8)
        if (isset($document->endtime) && (int)$document->endtime === 0) {
            unset($document->endtime);
        }

        $this->restoreFrontendEnvironment();

        return $document;
    }

    /**
     * @param Item $item
     * @throws SiteNotFoundException
     */
    protected function initializeFrontendEnvironment(Item $item)
    {
        $this->frontendHttpHost->initializeByRootPageId($item->getContext()->getSite()->getRootPageId());
    }

    /**
     * @param void
     */
    public function restoreFrontendEnvironment()
    {
        $this->frontendHttpHost->restore();
    }

    /**
     * @param ItemGroup $itemGroup
     * @param bool $merge
     * @return Document[]
     * @throws AspectNotFoundException
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws FileDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws SiteNotFoundException
     */
    public function createDocumentsForQueueItemGroup(ItemGroup $itemGroup, bool $merge = false): array
    {
        $documents = [];
        if ($merge) {
            // merging is active we merge all items into one solr document
            $rootItemDocument = $this->createDocumentForQueueItem($itemGroup->getRootItem());
            $configuration = $itemGroup->getRootItem()->getContext()->getSite()->getSolrConfiguration();
            $mapping = $configuration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.mergeDuplicates.fieldMapping.', []);
            $mapping = $this->addDefaultMergeMapping($mapping);

            foreach ($itemGroup->getItems() as $mergedItem) {
                // no merge items
                $mergeItemDocument = $this->createDocumentForQueueItem($mergedItem);
                foreach ($mapping as $sourceField => $targetField) {
                    $sourceFieldValue = $mergeItemDocument->$sourceField ?? null;

                    if ($sourceFieldValue === null) {
                        // nothing to add
                        continue;
                    }

                    $targetFieldValue = $this->getFieldValueAsArray($rootItemDocument->$targetField);
                    $valueAllReadyPresent = in_array($sourceFieldValue, $targetFieldValue);

                    if (!$valueAllReadyPresent) {
                        $rootItemDocument->addField($targetField, $sourceFieldValue);
                    }
                }
            }

            $documents[] = $rootItemDocument;
        } else {
            // no merging is active, we create one item per group
            foreach ($itemGroup->getItems() as $item) {
                $documents[] = $this->createDocumentForQueueItem($item);
            }
        }

        return $documents;
    }

    /**
     * Makes sure to retrieve the field value as an array.
     *
     * @param mixed $fieldValue
     * @return array
     */
    protected function getFieldValueAsArray($fieldValue): array
    {
        if (empty($fieldValue)) {
            return [];
        }
        if (is_array($fieldValue)) {
            return $fieldValue;
        }
        return [$fieldValue];
    }

    /**
     * @param array $mapping
     * @return array
     */
    protected function addDefaultMergeMapping(array $mapping): array
    {
        // we always want to merge the access field
        $mapping['access'] = 'access';

        return $mapping;
    }

    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * @param Document $document
     * @param Item $item
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws FileDoesNotExistException
     */
    protected function addFileInformation(Document $document, Item $item)
    {
        $file = $item->getFile();
        $publicUrl = FileUrlService::getInstance()->getPublicUrl(
            $file,
            $item->getContext()->getSite()->getTypo3SiteObject(),
            $item->getContext()->getLanguage()
        );

        // file meta data, reference
        $document
            ->setField('title', $file->getName())
            ->setField('created', $file->getCreationTime())
            ->setField('changed', $file->getModificationTime())
            ->setField('fileStorage', $file->getStorage()->getUid())
            ->setField('fileUid', $file->getUid())
            ->setField('fileMimeType', $file->getMimeType())
            ->setField('fileName', $file->getName())
            ->setField('fileSize', $file->getSize())
            ->setField('fileExtension', $file->getExtension())
            ->setField('fileSha1', $file->getSha1())

            ->setField('filePublicUrl', $publicUrl)
            ->setField('url', $publicUrl);

        $this->emitAddedSolrFileInformation($file);
    }

    /**
     * @param Document $document
     * @param Item $item
     * @throws FileDoesNotExistException
     * @throws NoSuitableExtractorFoundException
     * @throws ClientExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function addFileTextContent(Document $document, Item $item)
    {
        $file = $item->getFile();

        $extractorRegistry = GeneralUtility::makeInstance(TextExtractorRegistry::class);
        $extractor = $extractorRegistry->getTextExtractor($file);

        if (ExtensionManagementUtility::isLoaded('tika')) {
            $extractor = GeneralUtility::makeInstance(TextExtractor::class);
        }

        if (!is_object($extractor) || !method_exists($extractor, 'canExtractText')) {
            $message = 'No extractor for text extraction found for file ' . $file->getName() . '. Extractor registered or tika installed and solrfal file pattern configured correctly?';
            throw new NoSuitableExtractorFoundException($message);
        }

        if ($extractor->canExtractText($file)) {
            $fileTextContent = $extractor->extractText($file);
            $fileTextContent = HtmlContentExtractor::cleanContent($fileTextContent);

            $document->setField('content', $fileTextContent);
        }
    }

    /**
     * @param Document $document
     * @param Item $item
     * @throws DBALDriverException
     * @throws FileDoesNotExistException
     */
    protected function addContextInformation(Document $document, Item $item)
    {
        $context = $item->getContext();

        // Add additional context fields first, so they can't override necessary fields
        foreach ($context->getAdditionalStaticDocumentFields() as $key => $value) {
            $document->setField((string)$key, $value);
        }

        foreach ($context->getAdditionalDynamicDocumentFields($item->getFile()) as $key => $value) {
            $document->setField((string)$key, $value);
        }

        // system fields
        $document
            ->setField('uid', $item->getUid())
            ->setField('pid', $context->getPageId());

        /* @noinspection PhpUndefinedFieldInspection */
        if (!is_string($document->access)) {
            $accessRights = $context->getAccessRestrictions()->__toString();
            $document->setField('access', empty($accessRights) ? 'c:0' : $accessRights);
        }

        $document
            ->setField('id', $this->calculateDocumentId($item))
            ->setField('site', $context->getSite()->getDomain())
            ->setField('siteHash', $context->getSite()->getSiteHash())
            ->setField('appKey', 'EXT:solrfal')
            ->setField('type', self::SOLR_TYPE)
            ->setField(
                'variantId',
                $this->variantIdBuilder->buildFromTypeAndUid(
                    self::SOLR_TYPE,
                    // purpose of "?? 0" is Unit canSetDefaultsOnAccessFieldNullOrFalse(),
                    // which requires to many Mocks, since Core is currently not fully type-hinted.
                    $item->getFile()->getUid() ?? 0
                )
            );
    }

    /**
     * @param Document $document
     * @param Item $item
     * @throws AspectNotFoundException
     * @throws DBALDriverException
     * @throws FileDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function addFieldsFromTypoScriptConfiguration(Document $document, Item $item)
    {
        $translatedMetaData = $this->getTranslatedFileMetaData($item->getFile(), $item->getContext()->getLanguage());
        $this->emitFileMetaDataRetrieved($item, $translatedMetaData);

        $fileConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
            $item->getContext()->getSite()->getRootPageId(),
            $item->getContext()->getLanguage()
        );

        /* @var TypoScriptConfiguration $fileConfiguration */
        $fieldConfiguration = $fileConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.queue._FILES.default.', []);
        $contextConfiguration = $item->getContext()->getSpecificFieldConfigurationTypoScript();

        if (count($contextConfiguration) > 0) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $fieldConfiguration,
                $contextConfiguration
            );
        }

        $fieldConfigurationInRecordContext = null;
        if (is_array($fieldConfiguration['__RecordContext.'] ?? null)) {
            $fieldConfigurationInRecordContext = $fieldConfiguration['__RecordContext.'];
            unset($fieldConfiguration['__RecordContext']);
            unset($fieldConfiguration['__RecordContext.']);
        }

        FieldProcessingService::addTypoScriptFieldsToDocument($item->getContext(), $document, $fieldConfiguration, $translatedMetaData);

        if ($item->getContext() instanceof RecordContext && is_array($fieldConfigurationInRecordContext)) {
            /** @var RecordContext $recordContext */
            $recordContext = $item->getContext();
            $relatedRecord = BackendUtility::getRecord($recordContext->getTable(), $recordContext->getUid(), '*', '', false);
            $relatedRecord = $this->getTranslatedRecord($recordContext->getTable(), $relatedRecord, $recordContext->getLanguage());

            FieldProcessingService::addTypoScriptFieldsToDocument(
                $item->getContext(),
                $document,
                $fieldConfigurationInRecordContext,
                $relatedRecord
            );
        }
    }

    /**
     * @param File $file
     * @param int $language
     * @return array
     * @throws AspectNotFoundException
     */
    protected function getTranslatedFileMetaData(File $file, int $language = 0): array
    {
        $metaData = $file->getMetaData()->get();
        if ($metaData['sys_language_uid'] !== $language) {
            $metaData = $this->getTranslatedRecord('sys_file_metadata', $metaData, $language);
        }
        return $metaData;
    }

    /**
     * Retrieves a translated record with valid overlays according to the TCA configuration
     *
     * @param string $table
     * @param array $record
     * @param int $requestedLanguage
     * @return array
     * @throws AspectNotFoundException
     */
    protected function getTranslatedRecord(string $table, array $record, int $requestedLanguage = 0): array
    {
        $languageOfIncomingRecord = (int)$record[$GLOBALS['TCA'][$table]['ctrl']['languageField']];
        if (
            // default language requested and present
            !($requestedLanguage === 0 and $languageOfIncomingRecord <= 0) &&
            // but no parent field for overlays existing
            isset($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'])
        ) {
            $originalRecord = false;
            if ($languageOfIncomingRecord === 0) {
                $originalRecord = $record;
            } else {
                $originalRecordUid = (int)$record[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']];
                if ($originalRecordUid > 0) {
                    $originalRecord = BackendUtility::getRecord($table, $originalRecordUid, '*', '', false);
                }
            }
            if (is_array($originalRecord)) {
                if ((int)$originalRecord[$GLOBALS['TCA'][$table]['ctrl']['languageField']] !== $requestedLanguage) {
                    /** @var $overlayService OverlayService */
                    $overlayService = GeneralUtility::makeInstance(OverlayService::class);
                    $record = $overlayService->getRecordOverlay(
                        $table,
                        $record,
                        $requestedLanguage
                    );
                }
            }
        }
        return $record;
    }

    /**
     * @param Item $item
     *
     * @return string
     * @throws FileDoesNotExistException
     * @throws DBALDriverException
     */
    protected function calculateDocumentId(Item $item): string
    {
        return Util::getDocumentId(
            self::SOLR_TYPE,
            $item->getContext()->getPageId(),
            $item->getFile()->getUid(),
            md5(implode(',', $item->getContext()->toArray()))
        );
    }

    /**
     * @param File $file
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitAddedSolrFileInformation(File $file)
    {
        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'addedSolrFileInformation', [$file]);
    }

    /**
     * @param Item $item
     * @param array $metaData
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitFileMetaDataRetrieved(Item $item, array &$metaData)
    {
        $arrayObject = new ArrayObject($metaData);
        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'fileMetaDataRetrieved', [$item, &$arrayObject]);
        $metaData = $arrayObject->getArrayCopy();
    }
}
