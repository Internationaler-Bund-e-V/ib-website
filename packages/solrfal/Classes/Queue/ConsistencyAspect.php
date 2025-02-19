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

namespace ApacheSolrForTypo3\Solrfal\Queue;

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\RecordMonitor\Exception\RootPageRecordNotFoundException;
use ApacheSolrForTypo3\Solr\Domain\Index\Queue\RecordMonitor\Helper\RootPageResolver;
use ApacheSolrForTypo3\Solr\Domain\Site\Exception\UnexpectedTYPO3SiteInitializationException;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\Exception\InvalidArgumentException;
use ApacheSolrForTypo3\Solr\GarbageCollectorPostProcessor;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Cache\TwoLevelCache;
use ApacheSolrForTypo3\Solrfal\Context\ContextFactory;
use ApacheSolrForTypo3\Solrfal\Detection\RecordDetectionInterface;
use ApacheSolrForTypo3\Solrfal\Exception\Queue\UnknownStoragePageException;
use ApacheSolrForTypo3\Solrfal\Indexing\Indexer;
use ApacheSolrForTypo3\Solrfal\System\Configuration\ExtensionConfiguration;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Hooks and Slots taking care, the contents of the Queue only refer
 * to existing files and records, as well as purging Solr after
 * index queue entry is removed
 */
class ConsistencyAspect implements GarbageCollectorPostProcessor, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected Indexer $indexer;

    protected ItemRepository $itemRepository;

    protected ExtensionConfiguration $extensionConfiguration;

    /**
     * ConsistencyAspect constructor.
     */
    public function __construct(
        Indexer $indexer,
        ItemRepository $itemRepository,
        ExtensionConfiguration $extensionConfiguration,
    ) {
        $this->indexer = $indexer;
        $this->itemRepository = $itemRepository;
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function postProcessGarbageCollector(string $table, int $uid): void {}

    /**
     * If a file is deleted, we can/should remove it from Solr and index queue
     *
     * @throws DBALException
     */
    public function removeDeletedFile(FileInterface $file): int
    {
        if ($file instanceof File) {
            return $this->itemRepository->removeByFile($file);
        }
        return 0;
    }

    /**
     * If a file is marked as missing, we can/should remove it from Solr and index queue
     *
     * @throws DBALException
     */
    public function removeMissingFile(int $fileUid): int
    {
        return $this->itemRepository->removeByFileUid($fileUid);
    }

    /**
     * @throws UnexpectedTYPO3SiteInitializationException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws DBALException
     */
    public function fileIndexRecordUpdated(int $fileUid): void
    {
        $this->issueCommandOnDetectors('fileIndexRecordUpdated', 'sys_file', $fileUid);
    }

    /**
     * @throws UnexpectedTYPO3SiteInitializationException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws DBALException
     */
    public function fileIndexRecordCreated(int $fileId): void
    {
        $this->issueCommandOnDetectors('fileIndexRecordCreated', 'sys_file', $fileId);
    }

    /**
     * @throws UnexpectedTYPO3SiteInitializationException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws DBALException
     */
    public function fileIndexRecordDeleted(int $fileUid): void
    {
        $this->issueCommandOnDetectors('fileIndexRecordDeleted', 'sys_file', $fileUid);
    }

    /**
     * @throws UnexpectedTYPO3SiteInitializationException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws DBALException
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function processCmdmap_preProcess(
        string $command,
        string $table,
        int|string $id,
        mixed $value,
        DataHandler $pObj,
    ): void {
        $method = '';
        switch ($command) {
            case 'delete':
                $method = 'recordDeleted';
                break;
            default:
        }
        if ($method !== '') {
            $this->issueCommandOnDetectors($method, $table, (int)$id);
        }
    }

    /**
     * Executes/reacts on TYPO3 core hook after DB operation is persisted.
     *
     * @param string $status Status of the current operation, 'new' or 'update'
     * @param string $table The table the record belongs to
     * @param string|int $id The record's uid, [integer] or [string] (like 'NEW...')
     * @param array<string, mixed> $fieldArray
     * @param DataHandler $dataHandler The DataHandler instance to use
     *
     *
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws UnexpectedTYPO3SiteInitializationException
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string|int $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        // Check if record has been already been processed
        if ($this->hasRecordBeenProcessed($status, $table, $id)) {
            return;
        }

        $method = '';
        switch ($status) {
            case 'update':
                $method = 'recordUpdated';
                break;
            case 'new':
                $method = 'recordCreated';
                if (!MathUtility::canBeInterpretedAsInteger($id)
                    && isset($dataHandler->substNEWwithIDs[$id])
                    && MathUtility::canBeInterpretedAsInteger($dataHandler->substNEWwithIDs[$id])
                ) {
                    $id = $dataHandler->substNEWwithIDs[$id];
                } else {
                    return;
                }
                // no break
            default:
        }
        if ($method !== '') {
            $this->issueCommandOnDetectors($method, $table, (int)$id);
        }
    }

    /**
     * Checks if the record has already been processed by the processDatamap_afterDatabaseOperations hook
     *
     * @param string $status Status of the current operation, 'new' or 'update'
     * @param string $table The table the record belongs to
     * @param string|int $id The record's uid, [integer] or [string] (like 'NEW...')
     */
    protected function hasRecordBeenProcessed(string $status, string $table, string|int $id): bool
    {
        // Check if record has already been processed since DataHandler sends processDatamap_afterDatabaseOperations
        // more than one time per table with nearly identical $fields array - but we only use the pid
        // @see https://forge.typo3.org/issues/79635
        $cache = GeneralUtility::makeInstance(TwoLevelCache::class, 'runtime');
        $cacheId = 'ConsistencyAspect' . '_' . 'hasRecordBeenProcessed' . '_' . $table . '_' . $id . '_' . $status;

        $isProcessed = $cache->get($cacheId);
        if (!empty($isProcessed)) {
            // item already processed in this request
            return true;
        }
        $cache->set($cacheId, true);

        return false;
    }

    /**
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws UnexpectedTYPO3SiteInitializationException
     */
    protected function issueCommandOnDetectors(string $function, string $table, int $uid): void
    {
        try {
            $detectors = $this->getDetectorsForRecord($table, $uid);
        } catch (UnknownStoragePageException $e) {
            $detectors = [];
            $this->itemRepository->removeByTableAndUid($table, $uid);

            $this->logger->error(
                'Storage page couldn\'t be determined, items removed from queue and index',
                ['exception code' => $e->getCode(), 'exception' => $e->getMessage(), 'table' => $table, 'uid' => $uid]
            );
        }

        foreach ($detectors as $detector) {
            $detector->$function($table, $uid);
        }
    }

    /**
     * @return RecordDetectionInterface[]
     *
     * @throws UnexpectedTYPO3SiteInitializationException
     */
    protected function getDetectorsOfAllSites(): array
    {
        $sites = $this->getSiteRepository()->getAvailableSites();
        $detectors = [];
        foreach ($sites as $site) {
            $detectors = array_merge($detectors, ContextFactory::getContextDetectors($site));
        }
        return $detectors;
    }

    /**
     * Returns a site repository instance
     */
    protected function getSiteRepository(): SiteRepository
    {
        return GeneralUtility::makeInstance(SiteRepository::class);
    }

    /**
     * This method is used to determine the relevant detectors for a record.
     * For pages and content elements only the detector from the rootPage is relevant.
     *
     * For any other records all detectors will be invoked.
     *
     * @return RecordDetectionInterface[]
     *
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws RootPageRecordNotFoundException
     * @throws UnexpectedTYPO3SiteInitializationException
     * @throws UnknownStoragePageException
     */
    protected function getDetectorsForRecord(string $table, int $uid): array
    {
        $isSiteExclusiveRecord = $this->extensionConfiguration->getIsSiteExclusiveRecordTable($table);
        if ($isSiteExclusiveRecord) {
            return $this->getDetectorsForSiteExclusiveRecord($table, $uid);
        }

        // we have a normal record or a sys_file record. In these cases, we need to check all sites
        return $this->getDetectorsOfAllSites();
    }

    /**
     * This method is used to get all detectors for site exclusive records. Since we know that they
     * belong to one site, we only get the context detectors for this particular site.
     *
     * @return RecordDetectionInterface[]
     *
     * @throws DBALException
     * @throws UnknownStoragePageException
     * @throws RootPageRecordNotFoundException
     * @throws InvalidArgumentException
     */
    protected function getDetectorsForSiteExclusiveRecord(string $table, int $uid): array
    {
        $pageId = $this->getRecordPageId($table, $uid);
        /** @var RootPageResolver $rootPageResolver */
        $rootPageResolver = GeneralUtility::makeInstance(RootPageResolver::class);
        $rootPageId = $rootPageResolver->getRootPageId($pageId);

        if ($rootPageResolver->getIsRootPageId($rootPageId)) {
            // when we know that the page is a site root page, we can only get the
            // detectors for this site.
            $site = $this->getSiteRepository()->getSiteByPageId($rootPageId);

            return is_null($site) ? [] : ContextFactory::getContextDetectors($site);
        }

        // If rootPageId is not a root page - just return empty array since page/content element
        // is not part of a configured solr site
        return [];
    }

    /**
     * This method is used to get the page id that is relevant to build the root line.
     * If the record is a page the uid is used, if the record is not a page the pid of the record is used.
     *
     * @throws UnknownStoragePageException
     */
    protected function getRecordPageId(string $table, int $uid): int
    {
        if ($table === 'pages') {
            return $uid;
        }

        $record = BackendUtility::getRecord($table, $uid, 'uid,pid');
        if (!isset($record['pid'])) {
            throw new UnknownStoragePageException('Could not determine pid', 1661774890);
        }

        return (int)$record['pid'];
    }

    /**
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws NoSolrConnectionFoundException
     */
    public function removeSolrEntryForItem(Item $item): void
    {
        $this->indexer->removeFromIndex($item);
    }

    /**
     * @param int[] $uids
     *
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws UnexpectedTYPO3SiteInitializationException
     */
    public function removeMultipleQueueItemsFromSolr(array $uids): void
    {
        $sites = $this->getSiteRepository()->getAvailableSites();
        foreach ($sites as $site) {
            // HTTP exception is never thrown since EXT:solr change https://github.com/TYPO3-Solr/ext-solr/pull/3542
            $this->indexer->removeByQueueEntriesAndSite($uids, $site);
        }
    }
}
