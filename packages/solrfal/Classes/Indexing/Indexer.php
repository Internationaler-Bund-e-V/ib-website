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

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\Exception as ExtSolrException;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Exception\Exception as ExtSolrFrontendEnvironmentException;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Solr\ResponseAdapter;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;
use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterSingleFileDocumentOfItemGroupHasBeenIndexedEvent;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroupRepository;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Doctrine\DBAL\Exception as DBALException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Throwable;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Indexer
 */
class Indexer implements SingletonInterface
{
    /**
     * @throws FileDoesNotExistException
     * @throws DBALException
     */
    public function processIndexQueue(
        int $limit = 50,
        bool $evaluatePermissions = true,
        Site $limitToSite = null
    ): void {
        $limitToSiteId = is_null($limitToSite) ? 0 : $limitToSite->getRootPageId();
        $itemGroups = $this->getItemGroupRepository()->findAllIndexingOutStanding($limit, $limitToSiteId);
        foreach ($itemGroups as $itemGroup) {
            $this->tryToSetEvaluatePermissionsForGroupThenAddGroupToIndexOrMarkGroupAsFailed($itemGroup, $evaluatePermissions);
        }
    }

    /**
     * Tries to set whether the permissions to access or write into the file-storage should be checked or not.
     * If file does not exist in some Item, the whole ItemGroup is marked as failed.
     *
     * @throws FileDoesNotExistException
     */
    protected function tryToSetEvaluatePermissionsForGroupThenAddGroupToIndexOrMarkGroupAsFailed(
        ItemGroup $itemGroup,
        bool $evaluatePermissions,
    ): bool {
        $items = $itemGroup->getItems();
        if (count($items) === 0) {
            return false;
        }

        foreach ($items as $item) {
            try {
                $item->getFile()->getStorage()->setEvaluatePermissions($evaluatePermissions);
            } catch (FileDoesNotExistException $fileDoesNotExistException) {
                $this->markItemsInGroupAsFailed($itemGroup, vsprintf('File can not be indexed. Code: %s Message: %s', [$fileDoesNotExistException->getCode(), $fileDoesNotExistException->getMessage()]));
                return false;
            }
        }
        return $this->addGroupToIndex($itemGroup);
    }

    /**
     * @throws FileDoesNotExistException
     * @throws DBALException
     */
    public function addToIndex(Item $item): bool
    {
        $group = $this->getItemGroupRepository()->findByItem($item);
        return $this->addGroupToIndex($group);
    }

    /**
     * Adds a group of queue items to the solr index and applies merging or not.
     *
     * @throws FileDoesNotExistException
     */
    protected function addGroupToIndex(ItemGroup $group): bool
    {
        if (!$this->doesFileOfRootItemExist($group)) {
            $this->getItemRepository()->markFailed($group->getRootItem(), 'File missing');
            return false;
        }

        try {
            $success = $this->addGroupDocumentsToIndex($group);
        } catch (NoSolrConnectionFoundException) {
            $message = 'Invalid connection configuration for record';
            $this->markItemsInGroupAsFailed($group, $message);
            return false;
        } catch (Throwable $e) {
            $this->markItemsInGroupAsFailed($group, $e->getMessage());
            return false;
        }

        return $success;
    }

    /**
     * Checks if the file of the rootItem of the group exists.
     *
     * @throws FileDoesNotExistException
     */
    protected function doesFileOfRootItemExist(ItemGroup $group): bool
    {
        return $group->getRootItem()->getFile()->exists();
    }

    /**
     * Adds the group documents to the solr index.
     *
     * @throws AspectNotFoundException
     * @throws ClientExceptionInterface
     * @throws DBALException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtSolrException
     * @throws ExtSolrFrontendEnvironmentException
     * @throws FileDoesNotExistException
     * @throws NoSolrConnectionFoundException
     * @throws SiteNotFoundException
     */
    protected function addGroupDocumentsToIndex(ItemGroup $group): bool
    {
        $success = false;
        $merge = $this->getIsMergingEnabledFormItem($group->getRootItem());
        $documents = $this->getDocumentFactory()->createDocumentsForQueueItemGroup($group, $merge);
        $solr = $this->getSolrConnectionByContext($group->getRootItem()->getContext());

        foreach ($documents as $document) {
            unset($document->teaser);
            // todo document why teaser is unset

            $response = $solr->getWriteService()->addDocuments([$document]);

            $this->getEventDispatcher()->dispatch(
                new AfterSingleFileDocumentOfItemGroupHasBeenIndexedEvent(
                    $document,
                    $group,
                )
            );

            if ($response->getHttpStatus() == 200) {
                $success = true;
                foreach ($group->getItems() as $item) {
                    $this->getItemRepository()->markIndexedSuccessfully($item);
                }
            } else {
                $message = $response->getHttpStatusMessage() . ': ' . $response->getRawResponse();
                $this->markItemsInGroupAsFailed($group, $message);
            }
        }
        return $success;
    }

    /**
     * Marks all item's in a group as failed.
     */
    protected function markItemsInGroupAsFailed(ItemGroup $group, string $message): int
    {
        $markedAsFailedItemsCount = 0;
        foreach ($group->getItems() as $item) {
            $markedAsFailedItemsCount += $this->getItemRepository()->markFailed($item, $message);
        }
        return $markedAsFailedItemsCount;
    }

    /**
     * Removes a document from the solr index, which relates to the queue item
     *
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws NoSolrConnectionFoundException
     */
    public function removeFromIndex(Item $item): void
    {
        $merge = $this->getIsMergingEnabledFormItem($item);
        if ($merge) {
            // get group for item
            $group = $this->getItemGroupRepository()->findByItem($item);

            // we know that the group is not empty
            $wasRootItem = $group->getIsRootItem($item);
            $group->remove($item);
            $groupIsEmptyAfterRemove = $group->getIsEmpty();

            if ($wasRootItem || $groupIsEmptyAfterRemove) {
                // the item was the root document or the group is empty now => we can remove it from solr
                $this->removeFromIndexByItem($item);
            }

            if (!$groupIsEmptyAfterRemove) {
                $this->addGroupToIndex($group);
            }
        } else {
            $this->removeFromIndexByItem($item);
        }
    }

    protected function getIsMergingEnabledFormItem(Item $item): mixed
    {
        $configuration = $item->getContext()->getSite()->getSolrConfiguration();
        $merge = $configuration->getValueByPathOrDefaultValue('plugin.tx_solr.index.enableFileIndexing.mergeDuplicates', false);

        return filter_var($merge, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @throws NoSolrConnectionFoundException
     * @throws DBALException
     */
    protected function removeFromIndexByItem(Item $item): ResponseAdapter
    {
        $solr = $this->getSolrConnectionByContext($item->getContext());
        // build query, need to differentiate for the case when deleting whole pages
        $query = ['type:' . DocumentFactory::SOLR_TYPE, 'uid:' . $item->getUid()];

        // delete document(s) from index, directly commit
        return $solr->getWriteService()->deleteByQuery(implode(' AND ', $query));
    }

    /**
     * Removes the file index queue entries from given list of entries UIDs and Site object.
     *
     * @param int[] $uidArray The list of UIDs from file index queue entries to remove
     * @param Site $site The Site object to use for deletions
     * @param int<1, max> $chunkSize The chunk size to use for partial iterations
     *
     * @throws DBALException
     * @throws FileDoesNotExistException
     */
    public function removeByQueueEntriesAndSite(
        array $uidArray,
        Site $site,
        int $chunkSize = 1000,
    ): void {
        $chunks = array_chunk($uidArray, $chunkSize);
        $solrConnections = $this->getSolrConnectionsBySite($site);

        foreach ($chunks as $chunk) {
            $uidQueryPart = [];
            foreach ($chunk as $uid) {
                $uidQueryPart[] = 'uid:' . $uid;
                $this->indexNewRootDocumentWhenRequired($uid, $site);
            }

            $query = [
                'siteHash:' . $site->getSiteHash(),
                'type:' . DocumentFactory::SOLR_TYPE,
                '(' . implode(' OR ', $uidQueryPart) . ')',
            ];
            foreach ($solrConnections as $solr) {
                $solr->getWriteService()->deleteByQuery(implode(' AND ', $query));
            }
        }
    }

    protected function getDocumentFactory(): DocumentFactory
    {
        return GeneralUtility::makeInstance(DocumentFactory::class);
    }

    protected function getConnectionManager(): ConnectionManager
    {
        return GeneralUtility::makeInstance(ConnectionManager::class);
    }

    /**
     * @throws NoSolrConnectionFoundException
     * @throws DBALException
     */
    protected function getSolrConnectionByContext(ContextInterface $context): SolrConnection
    {
        return $this->getConnectionManager()
            ->getConnectionByRootPageId($context->getSite()->getRootPageId(), $context->getLanguage());
    }

    /**
     * @return SolrConnection[]
     */
    protected function getSolrConnectionsBySite(Site $site): array
    {
        $connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
        return $connectionManager->getConnectionsBySite($site);
    }

    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    protected function getItemGroupRepository(): ItemGroupRepository
    {
        return GeneralUtility::makeInstance(ItemGroupRepository::class);
    }

    /**
     * When merging is active and the root document of a mergeGroup is removed,
     * the mergeGroup needs to be re-added to make sure a new root document is determined.
     *
     * @throws DBALException
     * @throws FileDoesNotExistException
     */
    private function indexNewRootDocumentWhenRequired(
        int $uid,
        Site $site,
    ): void {
        $item = $this->getItemRepository()->findByUid($uid);
        if (is_null($item) || !$this->getIsMergingEnabledFormItem($item)) {
            return;
        }

        // Don't index from other sites, to prevent orphans, which will not be deleted by removeByQueueEntriesAndSite().
        if ($item->getContext()->getSite()->getSiteHash() !== $site->getSiteHash()) {
            return;
        }

        $group = $this->getItemGroupRepository()->findByItem($item);
        if (!$group->getIsRootItem($item)) {
            return;
        }

        $group->remove($item);
        if ($group->getIsEmpty()) {
            return;
        }

        // there are items left after removing, so we need to re-index the rest of the group
        $this->addGroupToIndex($group);
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }
}
