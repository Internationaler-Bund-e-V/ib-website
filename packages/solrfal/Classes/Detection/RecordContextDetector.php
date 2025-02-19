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

namespace ApacheSolrForTypo3\Solrfal\Detection;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Access\RootlineElement;
use ApacheSolrForTypo3\Solr\Access\RootlineElementFormatException;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Exception\Exception as SolrFrontendEnvironmentException;
use ApacheSolrForTypo3\Solrfal\Context\RecordContext;
use ApacheSolrForTypo3\Solrfal\Exception\Service\InvalidHookException as InvalidServiceHookException;
use ApacheSolrForTypo3\Solrfal\System\Language\OverlayService;
use ApacheSolrForTypo3\Solrfal\System\TCA\TCAService;
use Doctrine\DBAL\Exception as DBALException;
use PDO;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RecordContextDetector
 */
class RecordContextDetector extends AbstractRecordDetector
{
    /**
     * @inheritDoc
     *
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws UnknownLinkHandlerException
     */
    public function initializeQueue(
        string $indexingConfigurationName,
        ?bool $indexQueueForConfigurationNameIsInitialized = false
    ): bool {
        // only read them if indexing is enabled
        if (!$this->isIndexingEnabledForContext('record')) {
            // if it's disabled, remove everything
            $this->getItemRepository()->removeBySiteAndContext($this->site, 'record');
            return true;
        }

        $tableName = $this->siteConfiguration
            ->getIndexQueueTypeOrFallbackToConfigurationName($indexingConfigurationName);

        // remove relevant queue entries
        $this->getItemRepository()->removeByIndexingConfigurationInRecordContext($this->site, $indexingConfigurationName);
        if ($indexQueueForConfigurationNameIsInitialized
            && $this->isFileExtractionEnabledForIndexingConfiguration($indexingConfigurationName)
        ) {
            try {
                $this->initializeQueueForConfiguration($indexingConfigurationName, $tableName);
            } catch (Throwable $e) {
                $this->logger->error('Initialisation of "' . $indexingConfigurationName . '" failed: ' . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws UnknownLinkHandlerException
     *
     * @todo Move queries in EXT:Solr QueueItemRepository or implement extended from it Repository in EXT:Solrfal, Testing is then simpler.
     */
    protected function initializeQueueForConfiguration(
        string $indexingConfiguration,
        string $tableName,
    ): int {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_solr_indexqueue_item');
        $indexedRecords = $queryBuilder
            ->select('item_uid')
            ->from('tx_solr_indexqueue_item')
            ->where(
                $queryBuilder->expr()->eq('indexing_configuration', $queryBuilder->createNamedParameter($indexingConfiguration)),
                $queryBuilder->expr()->eq('root', $queryBuilder->createNamedParameter($this->site->getRootPageId(), PDO::PARAM_INT))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        if ($indexedRecords === []) {
            return 0;
        }

        $fields = $this->getFieldsToIndex($indexingConfiguration, $tableName);

        /** @var QueryBuilder $queryBuilderForRecords */
        $queryBuilderForRecords = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $records = $queryBuilderForRecords
            ->select('*')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->in('uid', $indexedRecords)
            )->executeQuery()
            ->fetchAllAssociative();

        $extractedQueueItems = 0;
        /** @var array{uid: int, pid: int} $record */
        foreach ($records as $record) {
            $extractedQueueItems += $this->extractQueueItemsFromRecord($indexingConfiguration, $tableName, $record, $fields);
        }
        return $extractedQueueItems;
    }

    /**
     * Calculates the fields which should be searched for files to index
     *
     * @return string[] The list of fields with attachments.
     *
     * @throws DBALException
     */
    protected function getFieldsToIndex(
        string $indexingConfiguration,
        string $tableName,
    ): array {
        $attachmentConfiguration = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.' . $indexingConfiguration . '.attachments.',
        );
        $fieldsInTable = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($tableName)
            ->createSchemaManager()
            ->listTableColumns($tableName);
        $fields = array_keys($fieldsInTable);
        if (array_key_exists('fields', $attachmentConfiguration)) {
            $fieldConfig = trim($attachmentConfiguration['fields']);
            if ($fieldConfig !== '*') {
                $requestedFields = GeneralUtility::trimExplode(',', $fieldConfig);
                $fields = array_intersect($fields, $requestedFields);
            }
        }
        return $fields;
    }

    /**
     * Extracts the file index queue items for given record in all languages.
     *
     * @param string $indexingConfiguration Indexing configuration name for requested solrfal-context record.
     * @param string $tableName The table name of given record
     * @param array{uid: int, pid: int}&array<string, string|int|bool> $record The given record to extract the file references from
     * @param string[] $fieldsToExtract The list of field names to extract the file references from
     * @param int[] $languagesToIndex The list of language UIDs to extract the file references from
     *
     * @return int Count of extracted file queue items
     *
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws UnknownLinkHandlerException
     */
    protected function extractQueueItemsFromRecord(
        string $indexingConfiguration,
        string $tableName,
        array $record,
        array $fieldsToExtract,
        ?array $languagesToIndex = null,
    ): int {
        if ($languagesToIndex === null) {
            $languagesToIndex = $this->site->getAvailableLanguageIds();
        } else {
            $availableLanguageIds = array_keys($this->site->getTypo3SiteObject()->getLanguages());
            $languagesToIndex = array_filter(
                $languagesToIndex,
                static function (int $languageId) use ($availableLanguageIds): bool {
                    return in_array($languageId, $availableLanguageIds);
                }
            );
        }

        if (empty($languagesToIndex)) {
            return 0;
        }

        $accessRootline = $this->getAccessRootlineByPageId($record['pid']);
        $extractedQueueItemsCount = 0;
        if (in_array(0, $languagesToIndex)) {
            $extractedQueueItemsCount = $this->extractQueueItemsForSingleTranslation(
                0,
                $indexingConfiguration,
                $tableName,
                $record,
                $fieldsToExtract,
                $accessRootline,
            );
        }

        foreach ($languagesToIndex as $languageUid) {
            if ($languageUid == 0) {
                continue;
            }
            $coreContextForLanguage = $this->getCoreContextForLanguageByTableNameAndRecord(
                $languageUid,
                $tableName,
                $record,
            );
            $translation = null;
            if ($coreContextForLanguage !== null) {
                /** @var OverlayService $overlayService */
                $overlayService = GeneralUtility::makeInstance(
                    OverlayService::class,
                    $coreContextForLanguage
                );

                $translation = $overlayService->getRecordOverlay(
                    $tableName,
                    $record,
                    (int)$languageUid
                );
            }
            if (!empty($translation)) {
                $extractedQueueItemsCount += $this->extractQueueItemsForSingleTranslation(
                    $languageUid,
                    $indexingConfiguration,
                    $tableName,
                    $translation,
                    $fieldsToExtract,
                    $accessRootline,
                );
            }
        }
        return $extractedQueueItemsCount;
    }

    protected function isFileExtractionEnabledForTable(string $tableName): bool
    {
        $enabled = $this->isFileExtractionEnabledForIndexingConfiguration($tableName);
        $queueConfig = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.',
        );

        if (!$enabled && !is_array($queueConfig[$tableName . '.'] ?? false)) {
            foreach ($queueConfig as $configurationName => $configuration) {
                $queueConfigTableName = $this->siteConfiguration
                    ->getIndexQueueTypeOrFallbackToConfigurationName($configurationName);
                if ($queueConfigTableName == $tableName
                    && ($queueConfig[$configurationName . '.']['attachments'] ?? false)
                ) {
                    $enabled = true;
                    break;
                }
            }
        }

        return $enabled;
    }

    protected function isFileExtractionEnabledForIndexingConfiguration(string $configurationKey): bool
    {
        $queueConfig = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.' . $configurationKey . '.',
        );
        return !empty($queueConfig['attachments']);
    }

    /**
     * Check if the file extension is configured to be allowed for indexing
     */
    protected function isAllowedFileExtension(File $file, string $indexingConfiguration = ''): bool
    {
        $config = [];

        // get file extensions
        if ($indexingConfiguration !== '') {
            $config = $this->siteConfiguration->getObjectByPathOrDefault(
                'plugin.tx_solr.index.queue.' . $indexingConfiguration . '.attachments.',
            );
        }

        // fileExtensions not set on context level check global index config
        if (!isset($config['fileExtensions'])) {
            $config = $this->siteConfiguration->getObjectByPathOrDefault(
                'plugin.tx_solr.index.enableFileIndexing.recordContext.',
            );
        }

        $extensions = $config['fileExtensions'] ?? '*';

        // evaluate extension list
        $extensions = strtolower(trim($extensions));
        if ($extensions === '*') {
            $isAllowedFileExtension = true;
        } else {
            $isAllowedFileExtension = GeneralUtility::inList($extensions, $file->getExtension());
        }

        return $isAllowedFileExtension;
    }

    /**
     * Returns the fields to index for given table
     *
     * @return string[] The EXT:solr queue configuration names with enabled `attachments`
     */
    protected function getIndexingConfigurationsForTable(string $tableName): array
    {
        $indexingConfigurations = [];
        $queueConfig = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.',
        );
        foreach ($queueConfig as $configurationName => $configuration) {
            $queueConfigTableName = $this->siteConfiguration
                ->getIndexQueueTypeOrFallbackToConfigurationName($configurationName);
            if ($queueConfigTableName == $tableName
                && ($configuration['attachments'] ?? false)
            ) {
                $indexingConfigurations[] = rtrim($configurationName, '.');
            }
        }

        return $indexingConfigurations;
    }

    /**
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws UnknownLinkHandlerException
     */
    public function recordCreated(
        string $table,
        int $uid,
    ): void {
        if (!($this->isIndexingEnabledForContext('record') && $this->isFileExtractionEnabledForTable($table))) {
            return;
        }

        // get record uid, considering translations
        $tcaService = $this->getTcaService();
        /** @var array{uid: int, pid: int}|null $record */
        $record = BackendUtility::getRecord($table, $uid);
        if ($record === null) {
            return;
        }
        $originalRecordUid = $tcaService->getTranslationOriginalUidIfTranslated(
            $table,
            $record,
            $uid
        );

        $limitToLanguages = null;
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'] ?? null;
        if ($record['uid'] === $originalRecordUid
            && $languageField !== null
            && ($record[$languageField] ?? 0) > 0
        ) {
            $limitToLanguages = [(int)$record[$languageField]];
        }

        $itemsInQueue = $this->getSolrQueueItemRepository()->findItems([$this->site], [], [$table], [$originalRecordUid]);
        if (!empty($itemsInQueue)) {
            foreach ($itemsInQueue as $itemInQueue) {
                $indexingConfiguration = $itemInQueue->getIndexingConfigurationName();
                /** @var array{uid: int, pid: int} $record|null */
                $record = BackendUtility::getRecord($table, $originalRecordUid, '*', '', false);
                if ($record === null) {
                    continue;
                }
                $this->extractQueueItemsFromRecord(
                    $indexingConfiguration,
                    $table,
                    $record,
                    $this->getFieldsToIndex($indexingConfiguration, $table),
                    $limitToLanguages
                );
            }
        } else {
            $this->getItemRepository()->removeByTableAndUidInContext('record', $this->site, $table, $originalRecordUid);
        }
    }

    /**
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws UnknownLinkHandlerException
     */
    public function recordUpdated(
        string $table,
        int $uid,
    ): void {
        // get referencing record if file reference was updated
        if ($table == 'sys_file_reference') {
            $fileReference = $this->getFileReferenceObject($uid);
            $table = $fileReference->getReferenceProperty('tablenames');
            $uid = $fileReference->getReferenceProperty('uid_foreign');
        }
        $this->recordCreated($table, $uid);
    }

    /**
     * @throws DBALException
     */
    public function recordDeleted(string $table, int $uid): void
    {
        if ($table == 'sys_file_reference') {
            // get reference and try to delete files of referencing records
            // Note that processCmdmap_preProcess must be used to call recordDeleted, otherwise
            // the file reference will not be available

            // resolve uid of original record, as original uid is used in queue
            $fileReference = $this->getFileReferenceObject($uid);
            if ($fileReference->getProperty('sys_language_uid') > 0) {
                $record = BackendUtility::getRecord(
                    $fileReference->getReferenceProperty('tablenames'),
                    $fileReference->getReferenceProperty('uid_foreign')
                );

                $contextRecordUid = $this->getTcaService()->getTranslationOriginalUidIfTranslated(
                    $fileReference->getReferenceProperty('tablenames'),
                    $record,
                    $fileReference->getReferenceProperty('uid_foreign')
                );
            } else {
                $contextRecordUid = $fileReference->getReferenceProperty('uid_foreign');
            }

            $this->getItemRepository()->removeByTableAndUidInContext(
                'record',
                $this->site,
                $fileReference->getReferenceProperty('tablenames'),
                $contextRecordUid,
                $fileReference->getReferenceProperty('uid_local')
            );
        } else {
            $tcaService = $this->getTcaService();
            $record = BackendUtility::getRecord($table, $uid);
            $originalRecordUid = $tcaService->getTranslationOriginalUidIfTranslated(
                $table,
                $record,
                $uid
            );
            $languageUid = $tcaService->getRecordLanguageUid($table, $record);

            $this->getItemRepository()->removeByTableAndUidInContext('record', $this->site, $table, $originalRecordUid, null, $languageUid);
        }
    }

    /**
     * Handles updates of sys_file entries
     *
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws UnknownLinkHandlerException
     */
    public function fileIndexRecordUpdated(
        string $table,
        int $uid
    ): void {
        // skip if record context is not enabled
        if (!$this->isIndexingEnabledForContext('record')) {
            return;
        }

        if ($table == 'sys_file') {
            $indexRecords = $this->getReferencedRecords($uid);
            foreach ($indexRecords as $recordData) {
                $this->recordCreated($recordData['table'], $recordData['uid']);
            }
        }
    }

    /**
     * Returns referenced records that have to be updated
     *
     * @return array<string, array{table: string, uid: int}>
     *
     * @throws DBALException
     */
    protected function getReferencedRecords(int $fileUid): array
    {
        $indexRecords = [];
        $referenceIndexRepository = $this->getReferenceIndexEntryRepository();

        // get and process references
        // skip reference in sys_file_metadata, assuming that record context will not be used to index this table
        // and pages table are considered in page context
        $references = $referenceIndexRepository->findByReferenceRecord(
            'sys_file',
            $fileUid,
            [
                'sys_file_metadata',
                'pages',
            ],
        );
        foreach ($references as $reference) {
            // try to get right reference to index record if reference refers to sys_file_reference
            if ($reference->getTableName() == 'sys_file_reference') {
                // get record reference if this is only a reference to sys_file_reference
                $reference = $referenceIndexRepository->findOneByReferenceIndexEntry(
                    $reference,
                    [
                        'sys_file_metadata',
                        'pages',
                    ]
                );
                if (!$reference) {
                    continue;
                }
            }

            $indexingConfigurations = $this->getIndexingConfigurationsForTable($reference->getTableName());
            if ($indexingConfigurations) {
                foreach ($indexingConfigurations as $indexingConfiguration) {
                    $fieldsToIndex = $this->getFieldsToIndex($indexingConfiguration, $reference->getTableName());
                    if (in_array($reference->getTableField(), $fieldsToIndex)) {
                        $indexRecords[$reference->getRecordHash()] = ['table' => $reference->getTableName(), 'uid' => $reference->getRecordUid()];
                    }
                }
            }
        }

        return $indexRecords;
    }

    /**
     * Extracts the file index queue items for given single translation.
     *
     * @param int $languageUid Requested language UID
     * @param string $indexingConfiguration Indexing configuration name for requested solrfal-context record.
     * @param string $tableName The table name of given record
     * @param array{uid: int, pid: int}&array<string, int|string|bool|null> $record The given record to extract the file references from
     * @param string[] $fieldsToExtract The list of field names to extract the file references from
     * @param Rootline $accessRootline The access rootline for given record
     *
     * @return int Count of extracted queue items for single translation
     *
     * @throws AspectNotFoundException
     * @throws DBALException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws RootlineElementFormatException
     * @throws UnknownLinkHandlerException
     */
    protected function extractQueueItemsForSingleTranslation(
        int $languageUid,
        string $indexingConfiguration,
        string $tableName,
        array $record,
        array $fieldsToExtract,
        Rootline $accessRootline,
    ): int {
        if (isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'])) {
            $groupAccess = $record[$GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group']];
        } else {
            $groupAccess = 0;
        }
        $currentRootline = clone $accessRootline;
        /** @var RootlineElement $accessRootlineElement */
        $accessRootlineElement = GeneralUtility::makeInstance(RootlineElement::class, 'r:' . $groupAccess);
        $currentRootline->push($accessRootlineElement);

        $allExtractedFileUids = [];
        foreach ($fieldsToExtract as $fieldName) {
            $fileUids = $this->getFileAttachmentResolver()->detectFilesInField($tableName, $fieldName, $record);
            // todo: what to do if file exists multiple times in different fields
            $successfulUids = [];

            $context = new RecordContext(
                $this->site,
                $currentRootline,
                $tableName,
                $fieldName,
                $record['uid'],
                $record['pid'],
                $indexingConfiguration,
                $languageUid
            );
            foreach ($fileUids as $fileUid) {
                try {
                    $file = $this->getResourceFactory()->getFileObject($fileUid);
                    if ($this->isAllowedFileExtension($file, $indexingConfiguration)) {
                        $indexQueueItem = $this->createQueueItem($file, $context);
                        if (!$this->getItemRepository()->exists($indexQueueItem)) {
                            $this->getItemRepository()->add($indexQueueItem);
                        } else {
                            $this->getItemRepository()->update($indexQueueItem);
                        }
                        $successfulUids[] = $allExtractedFileUids[] = $fileUid;
                    }
                } catch (ResourceDoesNotExistException) {
                    continue;
                }
            }
            $this->getItemRepository()->removeOldEntriesFromFieldInRecordContext($this->site, $tableName, $record['uid'], $languageUid, $fieldName, ...$successfulUids);
        }
        return count(array_unique($allExtractedFileUids));
    }

    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }

    protected function getTcaService(): TCAService
    {
        return GeneralUtility::makeInstance(TCAService::class);
    }
}
