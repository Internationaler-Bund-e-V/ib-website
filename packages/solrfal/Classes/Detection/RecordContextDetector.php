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
use ApacheSolrForTypo3\Solrfal\Context\RecordContext;
use ApacheSolrForTypo3\Solrfal\System\Language\OverlayService;
use Exception;
use PDO;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ClassRecordContextDetector
 */
class RecordContextDetector extends AbstractRecordDetector
{

    /**
     * @param array $initializationStatus
     * @throws Exception
     */
    public function initializeQueue(array $initializationStatus)
    {
        // only readd them if indexing is enabled
        if (!$this->isIndexingEnabledForContext('record')) {
            // if it's disabled, remove everything
            $this->getItemRepository()->removeBySiteAndContext($this->site, 'record');
            return;
        }

        foreach ($initializationStatus as $indexingConfigurationKey => $successfulInitialized) {
            $tableName = $indexingConfigurationKey;
            $indexConfig = $this->siteConfiguration->getObjectByPathOrDefault(
                'plugin.tx_solr.index.queue.' . $indexingConfigurationKey . '.',
                []
            );
            if (!empty($indexConfig['table'])) {
                $tableName = $indexConfig['table'];
            }
            // remove relevant queue entries
            $this->getItemRepository()->removeByIndexingConfigurationInRecordContext($this->site, $indexingConfigurationKey);
            if ($successfulInitialized && $this->isFileExtractionEnabledForIndexingConfiguration($indexingConfigurationKey)) {
                $this->initializeQueueForConfiguration($indexingConfigurationKey, $tableName);
            }
        }
    }

    /**
     * @param string $indexingConfiguration
     * @param string $tableName
     *
     *
     * @todo Move queries in EXT:Solr QueueItemRepository or implement extended from it Repository in EXT:Solrfal, Testing is then simpler.
     *
     * @throws Exception
     */
    protected function initializeQueueForConfiguration(string $indexingConfiguration, string $tableName)
    {
        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_solr_indexqueue_item');
        $indexedRecords = $queryBuilder
            ->select('item_uid')
            ->from('tx_solr_indexqueue_item')
            ->where(
                $queryBuilder->expr()->eq('indexing_configuration', $queryBuilder->createNamedParameter($indexingConfiguration)),
                $queryBuilder->expr()->eq('root', $queryBuilder->createNamedParameter($this->site->getRootPageId(), PDO::PARAM_INT))
            )->execute()->fetchAll(PDO::FETCH_COLUMN);

        if ($indexedRecords === []) {
            return;
        }

        $fields = $this->getFieldsToIndex($indexingConfiguration, $tableName);

        /* @var QueryBuilder $queryBuilderForRecords */
        $queryBuilderForRecords = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $records = $queryBuilderForRecords
            ->select('*')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->in('uid', $indexedRecords)
            )->execute()->fetchAll();

        foreach ($records as $record) {
            $this->extractQueueItemsFromRecord($indexingConfiguration, $tableName, $record, $fields);
        }
    }

    /**
     * Calculates the fields which should be searched for files to index
     *
     * @param string $indexingConfiguration
     * @param string $tableName
     *
     * @return array
     */
    protected function getFieldsToIndex($indexingConfiguration, $tableName): array
    {
        $attachmentConfiguration = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.' . $indexingConfiguration . '.attachments.',
            []
        );
        $fieldsInTable = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName)->getSchemaManager()->listTableColumns($tableName);
        $fields = array_keys($fieldsInTable);
        if (is_array($attachmentConfiguration) && array_key_exists('fields', $attachmentConfiguration)) {
            $fieldConfig = trim($attachmentConfiguration['fields']);
            if ($fieldConfig !== '*') {
                $requestedFields = GeneralUtility::trimExplode(',', $fieldConfig);
                $fields = array_intersect($fields, $requestedFields);
            }
        }
        return $fields;
    }

    /**
     * @param string $indexingConfiguration
     * @param string $tableName
     * @param array $record
     * @param array $fieldsToExtract
     * @throws Exception
     */
    protected function extractQueueItemsFromRecord($indexingConfiguration, $tableName, array $record, array $fieldsToExtract)
    {
        $accessRootline = Rootline::getAccessRootlineByPageId($this->site->getRootPageId());
        $languagesToIndex = $this->site->getAvailableLanguageIds();

        /** @var $overlayService OverlayService */
        $overlayService = GeneralUtility::makeInstance(OverlayService::class);

        $this->extractQueueItemsForSingleTranslation(0, $indexingConfiguration, $tableName, $record, $fieldsToExtract, $accessRootline);
        foreach ($languagesToIndex as $language) {
            if ($language == 0) {
                continue;
            }

            $translation = $overlayService->getRecordOverlay($tableName, $record, (int)$language, 'hideNonTranslated');
            if ($translation) {
                $this->extractQueueItemsForSingleTranslation($language, $indexingConfiguration, $tableName, $translation, $fieldsToExtract, $accessRootline);
            }
        }
    }

//    /**
//     * Returns a site repository instance
//     *
//     * @return SiteRepository
//     */
//    protected function getSiteRepository(): SiteRepository
//    {
//        /* @noinspection PhpIncompatibleReturnTypeInspection */
//        return GeneralUtility::makeInstance(SiteRepository::class);
//    }

    /**
     * @param string $tableName
     * @return bool
     */
    protected function isFileExtractionEnabledForTable($tableName): bool
    {
        $enabled = $this->isFileExtractionEnabledForIndexingConfiguration($tableName);
        $queueConfig = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.',
            []
        );
        if (!$enabled && !is_array($queueConfig[$tableName . '.'] ?? false)) {
            foreach ($queueConfig as $configuration) {
                if (is_array($configuration) && isset($configuration['table']) && $configuration['table'] == $tableName) {
                    $enabled = $configuration['attachments'] == 1;
                    break;
                }
            }
        }
        return $enabled;
    }

    /**
     * @param string $configurationKey
     * @return bool
     */
    protected function isFileExtractionEnabledForIndexingConfiguration($configurationKey): bool
    {
        $queueConfig = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.' . $configurationKey . '.',
            []
        );
        return !empty($queueConfig['attachments']);
    }

    /**
     * Check if the file extension is configured to be allowed for indexing
     *
     * @param File $file
     * @param string $indexingConfiguration
     *
     * @return bool
     */
    protected function isAllowedFileExtension(File $file, $indexingConfiguration = ''): bool
    {
        $config = [];

        // get file extensions
        if ($indexingConfiguration !== '') {
            $config = $this->siteConfiguration->getObjectByPathOrDefault(
                'plugin.tx_solr.index.queue.' . $indexingConfiguration . '.attachments.',
                []
            );
        }

        // fileExtensions not set on context level check global index config
        if (!isset($config['fileExtensions'])) {
            $config = $this->siteConfiguration->getObjectByPathOrDefault(
                'plugin.tx_solr.index.enableFileIndexing.recordContext.',
                []
            );
        }

        $extensions = isset($config['fileExtensions']) ? $config['fileExtensions'] : '*';

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
     * @param string $tableName
     * @return array
     */
    protected function getIndexingConfigurationsForTable($tableName): array
    {
        $indexingConfigurations = [];
        $queueConfig = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue.',
            []
        );
        foreach ($queueConfig as $configurationName => $configuration) {
            if (
                is_array($configuration)
                && ($configurationName == $tableName || (isset($configuration['table']) && $configuration['table'] == $tableName))
                && $configuration['attachments'] == 1
            ) {
                $indexingConfigurations[] = rtrim($configurationName, '.');
            }
        }

        return $indexingConfigurations;
    }

    /**
     * @param string $table
     * @param int $uid
     *
     * @throws Exception
     */
    public function recordCreated(string $table, int $uid)
    {
        if (!($this->isIndexingEnabledForContext('record') && $this->isFileExtractionEnabledForTable($table))) {
            return;
        }

        // fix record uid if record is a translation
        $originalRecordUid = $uid;
        if (isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])
            && ($record = BackendUtility::getRecord($table, $uid))
            && $record[$GLOBALS['TCA'][$table]['ctrl']['languageField']] > 0
        ) {
            $originalRecordUid = (int)$record[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']];
        }

        // @todo: On EXT:Solr >= 8.0 compatibility:
        //           Use original EXT:Solr QueueItemRepository method $this->getQueueItemRepository()->findItemsByItemTypeAndItemUid($table, $originalRecordUid) instead of fetching them here.
        /* @var QueryBuilder $queryBuilderForRecords */
        $queryBuilderForRecords = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_solr_indexqueue_item');
        $recordInIndexQueue = $queryBuilderForRecords
            ->select('indexing_configuration')
            ->from('tx_solr_indexqueue_item')
            ->andWhere(
                $queryBuilderForRecords->expr()->eq('item_type', $queryBuilderForRecords->quote($table, PDO::PARAM_STR)),
                $queryBuilderForRecords->expr()->eq('item_uid', $queryBuilderForRecords->quote($originalRecordUid, PDO::PARAM_INT)),
                $queryBuilderForRecords->expr()->eq('root', $this->site->getRootPageId())
            )->execute()->fetch();

        if ($recordInIndexQueue) {
            $indexingConfiguration = $recordInIndexQueue['indexing_configuration'];
            $record = BackendUtility::getRecord($table, $originalRecordUid, '*', '', false);
            $this->extractQueueItemsFromRecord(
                $indexingConfiguration,
                $table,
                $record,
                $this->getFieldsToIndex($indexingConfiguration, $table)
            );
        } else {
            $this->getItemRepository()->removeByTableAndUidInContext('record', $this->site, $table, $originalRecordUid);
        }
    }

    /**
     * @param string $table
     * @param int $uid
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function recordUpdated($table, $uid)
    {
        // get referencing record if file reference was updated
        if ($table == 'sys_file_reference') {
            $fileReference = $this->getFileReferenceObject($uid);
            $table = $fileReference->getReferenceProperty('tablenames');
            $uid = $fileReference->getReferenceProperty('uid_foreign');
        }
        $this->recordCreated($table, $uid);
    }

    /**
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function recordDeleted($table, $uid)
    {
        if ($table == 'sys_file_reference') {
            // get reference and try to delete files of referencing records
            // Note that processCmdmap_preProcess must be used to call recordDeleted, otherwise
            // the file reference will not be available

            $fileReference = $this->getFileReferenceObject($uid);
            $this->getItemRepository()->removeByTableAndUidInContext(
                'record',
                $this->site,
                $fileReference->getReferenceProperty('tablenames'),
                $fileReference->getReferenceProperty('uid_foreign'),
                $fileReference->getReferenceProperty('uid_local')
            );
        } else {
            $this->getItemRepository()->removeByTableAndUidInContext('record', $this->site, $table, $uid);
        }
    }

    /**
     * Handles updates of sys_file entries
     *
     * @param string $table
     * @param int $uid
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function fileIndexRecordUpdated($table, $uid)
    {
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
     * @param int $fileUid
     *
     * @return array
     */
    protected function getReferencedRecords($fileUid)
    {
        $indexRecords = [];
        $referenceIndexRepository = $this->getReferenceIndexEntryRepository();

        // get and process references
        // skip reference in sys_file_metadata, assuming that record context will not be used to indexed this table
        // and pages and pages_language_overlay tables are considered in page context
        $references = $referenceIndexRepository->findByReferenceRecord('sys_file', $fileUid, ['sys_file_metadata', 'pages', 'pages_language_overlay']);
        foreach ($references as $reference) {

            // try to get right reference to index record if reference refers to sys_file_reference
            if ($reference->getTableName() == 'sys_file_reference') {
                // get record reference if this is only a reference to sys_file_reference
                $reference = $referenceIndexRepository->findOneByReferenceIndexEntry($reference, ['sys_file_metadata', 'pages', 'pages_language_overlay']);
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
     * @param int $language
     * @param string $indexingConfiguration
     * @param $tableName
     * @param array $record
     * @param array $fieldsToExtract
     * @param Rootline $accessRootline
     * @throws Exception
     */
    protected function extractQueueItemsForSingleTranslation($language, $indexingConfiguration, $tableName, array $record, array $fieldsToExtract, Rootline $accessRootline)
    {
        if (isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']) && isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'])) {
            $groupAccess = $record[$GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group']];
        } else {
            $groupAccess = 0;
        }
        $currentRootline = clone $accessRootline;
        /* @var RootlineElement $accessRootlineElement */
        $accessRootlineElement = GeneralUtility::makeInstance(RootlineElement::class, 'r:' . $groupAccess);
        $currentRootline->push($accessRootlineElement);

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
                $indexingConfiguration,
                $language
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
                        $successfulUids[] = $fileUid;
                    }
                } catch (ResourceDoesNotExistException $e) {
                    continue;
                }
            }
            $this->getItemRepository()->removeOldEntriesFromFieldInRecordContext($this->site, $tableName, $record['uid'], $language, $fieldName, ...$successfulUids);
        }
    }

    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}
