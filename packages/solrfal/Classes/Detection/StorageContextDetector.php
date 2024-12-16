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
use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Solr\Service\SolrWriteService;
use ApacheSolrForTypo3\Solrfal\Context\StorageContext;
use Exception;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Resource\Exception as ResourceException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StorageContextDetector
 */
class StorageContextDetector extends AbstractRecordDetector
{

    /**
     * Folders
     *
     * @var array <storageUid> => <array of valid folders>
     */
    protected $folders = [];

    /**
     * Exclude folders
     *
     * @var array <storageUid> => <array of excluded folders>
     */
    protected $excludeFolders = [];

    /**
     * @param array $initializationStatus
     *
     * @throws Exception
     */
    public function initializeQueue(array $initializationStatus)
    {
        if (!empty($initializationStatus)) {
            // only read them if indexing is enabled
            if ($this->isIndexingEnabledForContext('storage')) {
                foreach ($initializationStatus as $indexingConfigurationIndex => $initializationResult) {
                    $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.queue.', []);
                    if (
                        isset($config[$indexingConfigurationIndex . '.']) &&
                        is_array($config[$indexingConfigurationIndex . '.']) &&
                        isset($config[$indexingConfigurationIndex . '.']['table']) &&
                        $config[$indexingConfigurationIndex . '.']['table'] === 'sys_file_storage' &&
                        isset($config[$indexingConfigurationIndex . '.']['storageUid'])
                    ) {
                        $fileStorageUid = (int)$config[$indexingConfigurationIndex . '.']['storageUid'];
                        // remove relevant queue entries
                        $this->getLogger()->info('Purging index-queue for storage ' . $fileStorageUid);
                        $this->getItemRepository()->removeByFileStorage($this->site, $fileStorageUid, $indexingConfigurationIndex);

                        $storage = $this->getStorageRepository()->findByUid($fileStorageUid);
                        if ($this->isIndexingEnabledForStorage($fileStorageUid) && $storage !== null) {
                            $this->getLogger()->info('Indexing storage ' . $fileStorageUid . ' is enabled for site ' . $this->site->getSiteHash());
                            $this->initializeQueueForStorage($storage, $indexingConfigurationIndex);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ResourceStorage $storage
     * @param string $indexingConfigurationIndex
     * @throws Exception
     */
    protected function initializeQueueForStorage(ResourceStorage $storage, $indexingConfigurationIndex = '')
    {
        $this->logger->debug('Starting indexing storage ' . $storage->getUid());
        $contexts = $this->getLanguageContextsForStorage($storage, $indexingConfigurationIndex);
        $fileExtensions = $this->getFileExtensionsToIndex($storage);

        $rootLevelFolder = $storage->getRootLevelFolder();
        if (count($fileExtensions)) {
            /** @var FileExtensionFilter $filter */
            $filter = GeneralUtility::makeInstance(FileExtensionFilter::class);
            $filter->setAllowedFileExtensions($fileExtensions);
            $rootLevelFolder->setFileAndFolderNameFilters([[$filter, 'filterFileList']]);
        }

        $itemRepository = $this->getItemRepository();
        $files = $rootLevelFolder->getFiles(0, 0, Folder::FILTER_MODE_USE_OWN_FILTERS, true);
        foreach ($files as $file) {
            if (($file instanceof ProcessedFile) || !$this->isAllowedFilePath($file)) {
                continue;
            }
            $this->logger->debug('Found file ' . $file->getCombinedIdentifier() . ' to be added to index-queue');
            foreach ($contexts as $context) {
                $itemRepository->add(
                    $this->createQueueItem(
                        $file,
                        $context
                    )
                );
            }
        }
    }

    /**
     * @param int $storageUid
     *
     * @return bool
     */
    protected function isIndexingEnabledForStorage($storageUid): bool
    {
        $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.storageContext.', []);
        return array_key_exists($storageUid . '.', $config);
    }

    /**
     * Determines the indexing configurations for given storage
     *
     * @param ResourceStorage $storage
     *
     * @return string[]
     */
    protected function getIndexingConfigurationsForStorage(ResourceStorage $storage): array
    {
        $indexingConfigurations = [];

        if ($this->isIndexingEnabledForStorage($storage->getUid())) {
            $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.queue.', []);
            foreach ($config as $configurationName => $configuration) {
                $configurationName = rtrim($configurationName, '.');

                if (
                    (int)($config[$configurationName] ?? null) === 1
                    && isset($configuration['storageUid']) && (int)$configuration['storageUid'] === $storage->getUid()
                    && ($configuration['table'] ?? null) === 'sys_file_storage'
                ) {
                    $indexingConfigurations[] = $configurationName;
                }
            }
        }

        return $indexingConfigurations;
    }

    /**
     * @param ResourceStorage $storage
     *
     * @return array
     */
    protected function getFileExtensionsToIndex(ResourceStorage $storage): array
    {
        $configuration = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.enableFileIndexing.storageContext.' . $storage->getUid() . '.',
            []
        );

        $fileExtensions = [];
        $configuredExtensions = trim($configuration['fileExtensions']);
        if ($configuredExtensions != '' && $configuredExtensions !== '*') {
            $fileExtensions = GeneralUtility::trimExplode(',', $configuredExtensions);
            $this->logger->debug('Indexing of storage ' . $storage->getUid() . ' will be restricted to file-extensions ' . $configuredExtensions);
        } else {
            $this->logger->debug('Indexing of storage ' . $storage->getUid() . ' will not be restricted to special file-extensions ');
        }

        return $fileExtensions;
    }

    /**
     * Check if the file extension is configured to be allowed for indexing
     *
     * @param File $file
     *
     * @return bool
     */
    protected function isAllowedFileExtension(File $file): bool
    {
        $allowedExtensions = $this->getFileExtensionsToIndex($file->getStorage());
        return $allowedExtensions === [] || in_array($file->getExtension(), $allowedExtensions);
    }

    /**
     * Check if the file path is allowed
     *
     * @param File $file
     * @return bool
     * @throws Exception
     */
    protected function isAllowedFilePath(File $file): bool
    {
        // check if file is in one of the configured valid folders
        $validFolders = $this->getValidFoldersForStorage($file->getStorage());
        $isAllowedFilePath = false;
        foreach ($validFolders as $validFolder) {
            $isAllowedFilePath = $file->getStorage()->isWithinFolder($validFolder, $file);
            if ($isAllowedFilePath) {
                break;
            }
        }

        // check if file is within an excluded folder
        if ($isAllowedFilePath) {
            $excludeFolders = $this->getExcludeFoldersForStorage($file->getStorage());
            foreach ($excludeFolders as $excludeFolder) {
                $isWithinExcludeFolder = $file->getStorage()->isWithinFolder($excludeFolder, $file);
                if ($isWithinExcludeFolder) {
                    $isAllowedFilePath = false;
                    break;
                }
            }
        }

        return $isAllowedFilePath;
    }

    /**
     * Returns the valid folders for given storage
     *
     * @param ResourceStorage $storage
     * @return Folder[]
     * @throws Exception
     */
    protected function getValidFoldersForStorage(ResourceStorage $storage): array
    {
        if (!isset($this->folders[$storage->getUid()])) {
            $this->folders[$storage->getUid()] = [];

            $configuredFolders = $this->siteConfiguration->getValueByPathOrDefaultValue('plugin.tx_solr.index.enableFileIndexing.storageContext.' . $storage->getUid() . '.folders', '*');
            if ($configuredFolders == '*') {
                $configuredFolders = ['/'];
            } else {
                $configuredFolders = GeneralUtility::trimExplode(',', $configuredFolders);
            }

            foreach ($configuredFolders as $folder) {
                try {
                    $this->folders[$storage->getUid()][] = $storage->getFolder($folder);
                } catch (Exception $e) {
                    $this->getLogger()->info('Invalid folder "' . $folder . '" configured for storage ' . $storage->getUid());
                }
            }
        }

        return $this->folders[$storage->getUid()];
    }

    /**
     * Returns the excluded folders for given storage
     *
     * @param ResourceStorage $storage
     * @return Folder[]
     * @throws Exception
     */
    protected function getExcludeFoldersForStorage(ResourceStorage $storage): array
    {
        if (!isset($this->excludeFolders[$storage->getUid()])) {
            $this->excludeFolders[$storage->getUid()] = [];

            $configuredExcludeFolders = $this->siteConfiguration->getValueByPathOrDefaultValue('plugin.tx_solr.index.enableFileIndexing.storageContext.' . $storage->getUid() . '.excludeFolders', '');
            if (!empty($configuredExcludeFolders)) {
                $configuredExcludeFolders = GeneralUtility::trimExplode(',', $configuredExcludeFolders);
                foreach ($configuredExcludeFolders as $excludeFolder) {
                    try {
                        $this->excludeFolders[$storage->getUid()][] = $storage->getFolder($excludeFolder);
                    } catch (ResourceException $e) {
                        $this->getLogger()->info('Invalid exclude folder "' . $excludeFolder . '" configured for storage ' . $storage->getUid());
                    }
                }
            }
        }

        return $this->excludeFolders[$storage->getUid()];
    }

    /**
     * @param ResourceStorage $storage
     * @param string $indexingConfiguration
     * @return StorageContext[]
     */
    protected function getLanguageContextsForStorage(ResourceStorage $storage, $indexingConfiguration = ''): array
    {
        $this->logger->debug('Creating contexts in which files need to be indexed for storage ' . $storage->getUid());
        $languages = $this->getLanguagesToIndexInStorage($storage);

        /** @var StorageContext[] $contexts * */
        $contexts = [];
        $accessRootline = Rootline::getAccessRootlineByPageId($this->site->getRootPageId());
        $this->logger->debug('Using access rootline of site root page (' . (string)$accessRootline . ') for storage ' . $storage->getUid());
        foreach ($languages as $languageUid) {
            $contexts[$languageUid] = new StorageContext(
                $this->site,
                $accessRootline,
                $indexingConfiguration,
                $languageUid
            );
            $contexts[$languageUid]->setAdditionalDocumentFields(['fileStorage' => $storage->getUid()]);
        }

        return $contexts;
    }

    /**
     * @param ResourceStorage $storage
     * @return int[]
     */
    protected function getLanguagesToIndexInStorage(ResourceStorage $storage): array
    {
        $configuration = $this->siteConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.enableFileIndexing.storageContext.' . $storage->getUid() . '.',
            []
        );
        $languages = [];
        if (isset($configuration['languages'])) {
            $languages = GeneralUtility::intExplode(',', trim($configuration['languages']));
        }
        if (count($languages) == 0) {
            $languages[] = 0;
        }
        $this->logger->debug(sprintf('Storage %d is configured to index languages "%s"', $storage->getUid(), implode(', ', $languages)));

        return $languages;
    }

    /**
     * @param string $table
     * @param int $uid
     * @throws Exception
     */
    public function recordCreated(string $table, int $uid)
    {
        if ($this->isIndexingEnabledForContext('storage')) {
            switch ($table) {
                case 'sys_file_storage':
                    if ($this->isIndexingEnabledForStorage($uid)) {
                        $storage = $this->getStorageRepository()->findByUid($uid);
                        if ($storage) {
                            $indexingConfigurations = $this->getIndexingConfigurationsForStorage($storage);
                            foreach ($indexingConfigurations as $indexingConfiguration) {
                                $this->initializeQueueForStorage($storage, $indexingConfiguration);
                            }
                        }
                    }
                    break;
                case 'sys_file_metadata':
                    // check if it is another language which has not been present before
                    break;
                case 'sys_file':
                    $file = $this->getFile($uid);
                    if (
                        $file instanceof File
                        && $this->isIndexingEnabledForStorage($file->getStorage()->getUid())
                        && $this->isAllowedFileExtension($file)
                        && $this->isAllowedFilePath($file)
                    ) {
                        $indexingConfigurations = $this->getIndexingConfigurationsForStorage($file->getStorage());
                        foreach ($indexingConfigurations as $indexingConfiguration) {
                            $this->logger->debug('New file created, adding to indexqueue (' . $indexingConfiguration . '): ' . $file->getCombinedIdentifier());

                            $languageContexts = $this->getLanguageContextsForStorage($file->getStorage(), $indexingConfiguration);
                            foreach ($languageContexts as $context) {
                                $this->getItemRepository()->add(
                                    $this->createQueueItem(
                                        $file,
                                        $context
                                    )
                                );
                            }
                        }
                    }
                    // no break
                default:
            }
        }
    }

    /**
     * @param string $table
     * @param int $uid
     * @throws Exception
     */
    public function recordUpdated(string $table, int $uid)
    {
        if (!$this->isIndexingEnabledForContext('storage')) {
            return;
        }

        $file = null;
        switch ($table) {
            case 'sys_file_storage':
                // do nothing if the storage changed
                // todo: think if it makes sense to reinitialize the search queue
                break;
            case 'sys_file_metadata':
                $fileRecord = BackendUtility::getRecord($table, $uid, 'file', '', false);
                $file = $this->getFile($fileRecord['file']);
                $this->logger->info('Metadata for file ' . $fileRecord['file'] . ' updated, marking queue-item for re-indexing');
                break;
            case 'sys_file':
                $file = $this->getFile($uid);
                $this->logger->info('Data of file ' . $uid . ' updated, marking queue-item for re-indexing');
                break;
            default:
        }

        if (
            $file instanceof File
            && $this->isIndexingEnabledForStorage($file->getStorage()->getUid())
            && $this->isAllowedFileExtension($file)
            && $this->isAllowedFilePath($file)
        ) {
            $itemsToUpdate = 0;
            $itemRepository = $this->getItemRepository();
            $indexingConfigurations = $this->getIndexingConfigurationsForStorage($file->getStorage());

            foreach ($indexingConfigurations as $indexingConfiguration) {
                $languageContexts = $this->getLanguageContextsForStorage($file->getStorage(), $indexingConfiguration);
                foreach ($languageContexts as $context) {
                    $queueItem = $this->createQueueItem($file, $context);
                    if ($itemRepository->exists($queueItem)) {
                        $itemsToUpdate++;
                    } else {
                        $this->logger->info('Add missing index queue item (' . $indexingConfiguration . ') for file ' . $file->getUid() . ' and language ' . $context->getLanguage());
                        $itemRepository->add($queueItem);
                    }
                }
            }

            // update existing queue items if any
            if ($itemsToUpdate) {
                $this->getItemRepository()->markFileUpdated($file->getUid(), ['context' => 'storage']);
            }
        }
    }

    /**
     * @param string $table
     * @param int $uid
     * @throws NoSolrConnectionFoundException
     * @noinspection PhpUnused
     */
    public function recordDeleted(string $table, int $uid)
    {
        switch ($table) {
            case 'sys_file_storage':
                // this really should not happen ever, even though admin may do that - do it, but ugly
                $configuration = $this->siteConfiguration->getObjectByPathOrDefault(
                    'plugin.tx_solr.index.enableFileIndexing.storageContext.',
                    []
                );
                if (array_key_exists($uid . '.', $configuration)) {
                    $this->logger->info('Indexed storage ' . $uid . ' has been removed. Clearing queue and solr index.');

                    $this->getItemRepository()->removeByFileStorage($this->site, $uid);
                    /* @var ConnectionManager $connectionManager */
                    $connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
                    $connections = $connectionManager->getConnectionsBySite($this->site);
                    /* @var SolrWriteService $solrService */
                    foreach ($connections as $solrService) {
                        $solrService->deleteByQuery('fileStorage:' . (int)$uid);
                    }
                }
                break;
            case 'sys_file_metadata':
                // ignore this, in normal conditions this never happens / should happen
            case 'sys_file':
                // File deletion is taken care of via Slot do deletion signal
                break;
            default:
        }
    }

    /**
     * Handles new sys_file entries
     *
     * @param string $table
     * @param int $uid
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function fileIndexRecordCreated(string $table, int $uid)
    {
        $this->recordCreated($table, $uid);
    }

    /**
     * Handles updates on sys_file entries
     *
     * @param string $table
     * @param int $uid
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function fileIndexRecordUpdated(string $table, int $uid)
    {
        $this->recordUpdated($table, $uid);
    }
}
