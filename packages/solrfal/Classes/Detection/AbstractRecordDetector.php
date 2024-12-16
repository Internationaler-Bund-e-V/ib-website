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

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use ApacheSolrForTypo3\Solrfal\Domain\Repository\ReferenceIndexEntryRepository;
use ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Service\FileAttachmentResolver;
use Exception;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractRecordDetector
 */
abstract class AbstractRecordDetector implements RecordDetectionInterface
{

    /**
     * @var Site
     */
    protected $site;

    /**
     * @var array
     */
    protected $siteConfiguration;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Site $site
     * @param TypoScriptConfiguration|null $siteConfiguration
     * @noinspection PhpUnused
     */
    public function __construct(Site $site, TypoScriptConfiguration $siteConfiguration = null)
    {
        $this->site              = $site;
        $this->siteConfiguration = is_null($siteConfiguration) ? $site->getSolrConfiguration() : $siteConfiguration;
        $this->logger            = $this->getLogger();
    }

    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        if ($this->logger === null) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * Checks if the Indexing is Enabled
     *
     * @param string $context ContextType
     *
     * @return bool
     */
    protected function isIndexingEnabledForContext(string $context): bool
    {
        $contextConfig = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.', []);
        return !empty($contextConfig[$context . 'Context']);
    }

    /**
     * @return ItemRepository
     */
    protected function getItemRepository(): ItemRepository
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    /**
     * @return StorageRepository
     */
    protected function getStorageRepository(): StorageRepository
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(StorageRepository::class);
    }

    /**
     * @return FileAttachmentResolver
     */
    protected function getFileAttachmentResolver(): FileAttachmentResolver
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(FileAttachmentResolver::class);
    }

    /**
     * Returns the reference index entry repository
     *
     * @return ReferenceIndexEntryRepository
     */
    protected function getReferenceIndexEntryRepository(): ReferenceIndexEntryRepository
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ReferenceIndexEntryRepository::class);
    }

    /**
     * Returns the indexing queue
     *
     * @return Queue
     */
    protected function getIndexQueue(): Queue
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(Queue::class);
    }

    /**
     * Returns a file object
     *
     * @param int $fileUid
     *
     * @return File|null
     */
    protected function getFile(int $fileUid): ?File
    {
        $file = null;
        /* @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $file = $resourceFactory->getFileObject((int)$fileUid);
        } catch (ResourceDoesNotExistException $e) {
            $this->logger->error('File not found: ' . $fileUid);
        } catch (Exception $e) {
            $this->logger->error(
                'Unknown exception while loading file: ' . $fileUid . PHP_EOL .
                'Code: ' . $e->getCode() . PHP_EOL .
                'Message: ' . $e->getMessage()
            );
        }

        return $file;
    }

    /**
     * Returns a file reference object
     *
     * We use this own method since ResourceFactory caches the
     * file reference objects and we cannot be sure that this
     * object is up-to-date
     *
     * @param int $fileReferenceUid
     *
     * @return FileReference|null
     */
    protected function getFileReferenceObject(int $fileReferenceUid): ?FileReference
    {
        $fileReference = null;

        try {
            $fileReferenceData = BackendUtility::getRecord('sys_file_reference', $fileReferenceUid);
            $fileReference = $this->getResourceFactory()->createFileReferenceObject($fileReferenceData);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $fileReference;
    }

    /**
     * Creates a new queue item
     *
     * @param File $file
     * @param ContextInterface $context
     *
     * @return Item
     */
    protected function createQueueItem(File $file, ContextInterface $context): Item
    {
        /** @var $item Item */
        $item = GeneralUtility::makeInstance(Item::class, $file->getUid(), $context);

        // a file should be unique per file, language and site
        $mergeId =  DocumentFactory::SOLR_TYPE . '/' .
                    $file->getUid() . '/' .
                    $context->getLanguage() . '/' .
                    $context->getSite()->getRootPageId();

        $item->setMergeId($mergeId);

        return $item;
    }

    /**
     * Handles new sys_file entries
     *
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function fileIndexRecordCreated(string $table, int $uid)
    {
        // handle creation of sys_file records, presumably only relevant for storage context
    }

    /**
     * Handles deletions of sys_file entries
     *
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function fileIndexRecordDeleted(string $table, int $uid)
    {
        // TODO: check if action is required, since file deletion is also taken care of via Slot postFileDelete
        $this->getItemRepository()->removeByFileUid($uid);
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
