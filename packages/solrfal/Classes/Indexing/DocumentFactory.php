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
use ApacheSolrForTypo3\Solr\Exception as ExtSolrException;
use ApacheSolrForTypo3\Solr\FrontendEnvironment;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Exception\Exception;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solr\HtmlContentExtractor;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solr\Util;
use ApacheSolrForTypo3\Solrfal\Context\PageContext;
use ApacheSolrForTypo3\Solrfal\Context\RecordContext;
use ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileInfoHasBeenAddedToDocumentEvent;
use ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileMetaDataHasBeenRetrievedEvent;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;
use ApacheSolrForTypo3\Solrfal\Service\FieldProcessingService;
use ApacheSolrForTypo3\Solrfal\Service\FileUrlService;
use ApacheSolrForTypo3\Solrfal\System\Environment\FrontendServerEnvironment;
use ApacheSolrForTypo3\Solrfal\System\Language\OverlayService;
use Doctrine\DBAL\Exception as DBALException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\TextExtraction\TextExtractorInterface;
use TYPO3\CMS\Core\Resource\TextExtraction\TextExtractorRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class DocumentFactory
 */
class DocumentFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const SOLR_TYPE = 'tx_solr_file';

    protected IdBuilder $variantIdBuilder;

    protected FrontendServerEnvironment $frontendHttpHost;

    /**
     * DocumentFactory constructor.
     */
    public function __construct(
        IdBuilder $variantIdBuilder = null,
        FrontendServerEnvironment $frontendHttpHost = null
    ) {
        $this->variantIdBuilder = $variantIdBuilder ?? GeneralUtility::makeInstance(IdBuilder::class);
        $this->frontendHttpHost = $frontendHttpHost ?? GeneralUtility::makeInstance(FrontendServerEnvironment::class);
    }

    /**
     * @throws AspectNotFoundException
     * @throws ClientExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtSolrException
     * @throws FileDoesNotExistException
     * @throws SiteNotFoundException
     */
    public function createDocumentForQueueItem(Item $item): Document
    {
        $this->initializeFrontendEnvironment($item);

        /** @var Document $document */
        $document = GeneralUtility::makeInstance(Document::class);

        $this->addFileInformation($document, $item);

        try {
            $this->addFileTextContent($document, $item);
        } catch (NoSuitableExtractorFoundException $e) {
            // no extractor found to extract text, we might want to index
            // the document without the text content but log an info message
            $this->logger->info($e->getMessage());
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
     * @throws SiteNotFoundException
     */
    protected function initializeFrontendEnvironment(Item $item): void
    {
        $this->frontendHttpHost->initializeByRootPageId($item->getContext()->getSite()->getRootPageId());
    }

    public function restoreFrontendEnvironment(): void
    {
        $this->frontendHttpHost->restore();
    }

    /**
     * @return Document[]
     *
     * @throws AspectNotFoundException
     * @throws ClientExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws ExtSolrException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws FileDoesNotExistException
     * @throws SiteNotFoundException
     */
    public function createDocumentsForQueueItemGroup(ItemGroup $itemGroup, bool $merge = false): array
    {
        $documents = [];
        if ($merge) {
            // merging is active we merge all items into one solr document
            $rootItemDocument = $this->createDocumentForQueueItem($itemGroup->getRootItem());
            $configuration = $itemGroup->getRootItem()->getContext()->getSite()->getSolrConfiguration();
            $mapping = $configuration->getObjectByPathOrDefault(
                'plugin.tx_solr.index.enableFileIndexing.mergeDuplicates.fieldMapping.',
            );
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
     * @param string|string[]|null $fieldValue
     * @return string[]
     *
     * @todo: Check if $fieldValue can be null at all.
     */
    protected function getFieldValueAsArray(null|string|array $fieldValue): array
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
     * @param array<string, string|int|bool|null>|array<string,mixed> $mapping
     * @return array<string, string|int|bool|null>|array<string,mixed>
     */
    protected function addDefaultMergeMapping(array $mapping): array
    {
        // we always want to merge the access field
        $mapping['access'] = 'access';

        return $mapping;
    }

    /**
     * @throws FileDoesNotExistException
     */
    protected function addFileInformation(
        Document $document,
        Item $item,
    ): Document {
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

        /** @var AfterFileInfoHasBeenAddedToDocumentEvent $event */
        $event = $this->getEventDispatcher()->dispatch(
            new AfterFileInfoHasBeenAddedToDocumentEvent(
                $document,
                $item,
                $item->getContext()->getSite()->getTypo3SiteObject(),
                $file,
            )
        );
        return $event->getDocument();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws FileDoesNotExistException
     * @throws NoSuitableExtractorFoundException
     */
    protected function addFileTextContent(
        Document $document,
        Item $item,
    ): void {
        $file = $item->getFile();

        /** @var TextExtractorRegistry $extractorRegistry */
        $extractorRegistry = GeneralUtility::makeInstance(TextExtractorRegistry::class);
        $extractors = array_filter(
            $extractorRegistry->getTextExtractorInstances(),
            static fn(TextExtractorInterface $extractor): bool => $extractor->canExtractText($file)
        );

        if ($extractors === []) {
            $message = 'No extractors for text extraction found for file ' . $file->getName() . '.'
                . ' None of the registered text extractors indicated that an extraction is possible (see TextExtractorInterface->canExtractText).';
            throw new NoSuitableExtractorFoundException($message);
        }

        foreach ($extractors as $extractor) {
            try {
                $fileTextContent = trim($extractor->extractText($file));
                if ($fileTextContent === '') {
                    continue;
                }

                $fileTextContent = HtmlContentExtractor::cleanContent($fileTextContent);
                $document->setField('content', $fileTextContent);
                break;
            } catch (Throwable $e) {
                $this->logger->error(
                    'Registered text extractor ' . get_class($extractor)
                    . ' failed to extract contents of ' . $file->getIdentifier()
                    . ': ' . $e->getMessage(),
                    [$e->getCode(), $e->getFile(), $e->getMessage()]
                );
            }
        }
    }

    /**
     * @throws DBALException
     * @throws FileDoesNotExistException
     */
    protected function addContextInformation(
        Document $document,
        Item $item,
    ): void {
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
            ->setField('pid', $context->getPid());

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
                    // purpose of "?? 0" and "?? []" is Unit canSetDefaultsOnAccessFieldNullOrFalse(),
                    // which requires to many Mocks, since Core is currently not fully type-hinted.
                    $item->getFile()->getUid() ?? 0,
                    $item->getFile()->toArray() ?? [],
                    $item->getContext()->getSite(),
                    $document,
                )
            );
    }

    /**
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws Exception
     * @throws SiteNotFoundException
     */
    protected function addFieldsFromTypoScriptConfiguration(
        Document $document,
        Item $item,
    ): void {
        /** @var AfterFileMetaDataHasBeenRetrievedEvent $afterFileMetaDataHasBeenRetrievedEvent */
        $afterFileMetaDataHasBeenRetrievedEvent = $this->getEventDispatcher()->dispatch(
            new AfterFileMetaDataHasBeenRetrievedEvent(
                $this->getTranslatedFileMetaData(
                    $item,
                    $item->getContext()->getLanguage(),
                ),
                $document,
                $item,
            )
        );
        $translatedMetaData = $afterFileMetaDataHasBeenRetrievedEvent->getMetaData();

        $fileConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
            $item->getContext()->getSite()->getRootPageId(),
            $item->getContext()->getLanguage()
        );

        /** @var TypoScriptConfiguration $fileConfiguration */
        $fieldConfiguration = $fileConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue._FILES.default.',
        );
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
            /** @var RecordContext|PageContext $recordContext */
            $recordContext = $item->getContext();
            /** @var array{uid: int, pid: int} $relatedRecord */
            $relatedRecord = BackendUtility::getRecord($recordContext->getTable(), $recordContext->getUid(), '*', '', false);
            $relatedRecord = $this->getTranslatedRecordForItemAndTable(
                $item,
                $recordContext->getTable(),
                $relatedRecord,
                $recordContext->getLanguage(),
            ) ?? [];

            FieldProcessingService::addTypoScriptFieldsToDocument(
                $item->getContext(),
                $document,
                $fieldConfigurationInRecordContext,
                $relatedRecord
            );
        }
    }

    /**
     * @return array<string, string|int|bool|null>|array<string,mixed> The file metadata record
     *
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws FileDoesNotExistException
     */
    protected function getTranslatedFileMetaData(Item $item, int $language = 0): array
    {
        /** @var array{uid: int, pid: int, sys_language_uid: int} $metaData */
        $metaData = $item->getFile()->getMetaData()->get();
        if (($metaData['sys_language_uid'] ?? null) !== $language) {
            $metaData = $this->getTranslatedRecordForItemAndTable(
                $item,
                'sys_file_metadata',
                $metaData,
                $language,
            ) ?? $metaData;
        }
        return $metaData;
    }

    /**
     * Retrieves a translated record with valid overlays according to the TCA configuration
     *
     * @param Item $item The file index queue object
     * @param string $table The table name of given record
     * @param array{uid: int, pid: int} $record The record to fetch the translation for requested language
     * @param int $requestedLanguage The language to fetch the translation for
     *
     * @return array<string, string|int|bool|null>|array<string,mixed>|null
     *
     * @throws AspectNotFoundException
     * @throws DBALException
     */
    protected function getTranslatedRecordForItemAndTable(
        Item $item,
        string $table,
        array $record,
        int $requestedLanguage = 0,
    ): ?array {
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
            $coreContext = $this->getCoreContextForItemAndLanguage($item, $requestedLanguage);
            if (is_array($originalRecord) && $coreContext !== null) {
                if ((int)$originalRecord[$GLOBALS['TCA'][$table]['ctrl']['languageField']] !== $requestedLanguage) {
                    /** @var OverlayService $overlayService */
                    $overlayService = GeneralUtility::makeInstance(
                        OverlayService::class,
                        $coreContext
                    );
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
     * @throws DBALException
     * @throws FileDoesNotExistException
     */
    protected function calculateDocumentId(Item $item): string
    {
        return Util::getDocumentId(
            self::SOLR_TYPE,
            $item->getContext()->getSite()->getRootPageId(),
            $item->getFile()->getUid(),
            md5(implode(',', $item->getContext()->toArray()))
        );
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    protected function getCoreContextForItemAndLanguage(
        Item $item,
        int $languageUid,
    ): ?Context {
        try {
            $tsfe = GeneralUtility::makeInstance(Tsfe::class)
                ->getTsfeByPageIdAndLanguageId(
                    $item->getContext()->getPidForCoreContext(),
                    $languageUid,
                );
            $coreContext = $tsfe->getContext();

            // set access restrictions from solrfal context
            $coreContext->setAspect(
                'frontend.user',
                GeneralUtility::makeInstance(
                    UserAspect::class,
                    null,
                    $item->getContext()->getAccessRestrictions()->getGroups()
                )
            );

            return $coreContext;
        } catch (Throwable $exception) {
            $this->logger->error(
                '{className} can not instantiate {TsfeFqcn} for PID:{PID} and {languageUid}, due of exception: {exceptionInfo} {exceptionTrace}',
                [
                    'className' => self::class,
                    'TsfeFqcn' => TypoScriptFrontendController::class,
                    'PID' => $item->getContext()->getSite()->getRootPageId(),
                    'languageUid' => $languageUid,
                    'exceptionInfo' => $exception->getCode() . 'in' . $exception->getFile() . 'line ' . $exception->getLine(),
                    'exceptionTrace' => $exception->getTraceAsString(),
                ]
            );
            return null;
        }
    }
}
