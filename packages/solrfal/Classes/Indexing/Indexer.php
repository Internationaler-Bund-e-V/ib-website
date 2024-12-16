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
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;
use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroupRepository;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class Indexer
 */
class Indexer implements SingletonInterface
{
    /**
     * @param int $limit
     * @param bool $evaluatePermissions
     * @param Site|null $limitToSite
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function processIndexQueue(
        int $limit = 50,
        bool $evaluatePermissions = true,
        Site $limitToSite = null
    ) {
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
     * @param ItemGroup $itemGroup
     * @param bool $evaluatePermissions
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function tryToSetEvaluatePermissionsForGroupThenAddGroupToIndexOrMarkGroupAsFailed(ItemGroup $itemGroup, bool $evaluatePermissions)
    {
        foreach ($itemGroup->getItems() as $item) {
            try {
                $item->getFile()->getStorage()->setEvaluatePermissions($evaluatePermissions);
            } catch (FileDoesNotExistException $fileDoesNotExistException) {
                $this->markItemsInGroupAsFailed($itemGroup, vsprintf('File can not be indexed. Code: %s Message: %s', [$fileDoesNotExistException->getCode(), $fileDoesNotExistException->getMessage()]));
                return;
            }
        }
        $this->addGroupToIndex($itemGroup);
    }

    /**
     * @param Item $item
     *
     * @return bool
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addToIndex(Item $item): bool
    {
        $group = $this->getItemGroupRepository()->findByItem($item);
        return $this->addGroupToIndex($group);
    }

    /**
     * Adds a group of queue items to the solr index and applies merging or not.
     *
     * @param ItemGroup $group
     * @return bool
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function addGroupToIndex(ItemGroup $group): bool
    {
        if (!$this->doesFileOfRootItemExist($group)) {
            $this->getItemRepository()->markFailed($group->getRootItem(), 'File missing');
            return false;
        }

        try {
            $success = $this->addGroupDocumentsToIndex($group);
        } catch (NoSolrConnectionFoundException $e) {
            $message = 'Invalid connection configuration for record';
            $this->markItemsInGroupAsFailed($group, $message);
            return false;
        } catch (Exception $e) {
            $this->markItemsInGroupAsFailed($group, $e->getMessage());
            return false;
        }

        return $success;
    }

    /**
     * Checks if the file of the rootItem of the group exists.
     *
     * @param ItemGroup $group
     * @return bool
     * @throws FileDoesNotExistException
     */
    protected function doesFileOfRootItemExist(ItemGroup $group): bool
    {
        return $group->getRootItem()->getFile()->exists();
    }

    /**
     * Adds the group documents to the solr index.
     *
     * @param ItemGroup $group
     * @return bool
     * @throws AspectNotFoundException
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws FileDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws NoSolrConnectionFoundException
     * @throws SiteNotFoundException
     * @throws \Doctrine\DBAL\DBALException
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
            $this->emitIndexedFileToSolr($group->getRootItem()->getFile());

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
     *
     * @param ItemGroup $group
     * @param string $message
     * @throws DBALException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function markItemsInGroupAsFailed(ItemGroup $group, string $message)
    {
        foreach ($group->getItems() as $item) {
            $this->getItemRepository()->markFailed($item, $message);
        }
    }

    /**
     * Removes a document from the solr index, which relates to the queue item
     *
     * @param Item $item
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws NoSolrConnectionFoundException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeFromIndex(Item $item)
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

    /**
     * @param Item $item
     * @return mixed
     */
    protected function getIsMergingEnabledFormItem(Item $item)
    {
        $configuration = $item->getContext()->getSite()->getSolrConfiguration();
        $merge = $configuration->getValueByPathOrDefaultValue('plugin.tx_solr.index.enableFileIndexing.mergeDuplicates', false);

        return filter_var($merge, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param Item $item
     * @throws DBALDriverException
     * @throws NoSolrConnectionFoundException
     */
    protected function removeFromIndexByItem(Item $item)
    {
        $solr = $this->getSolrConnectionByContext($item->getContext());
        // build query, need to differentiate for the case when deleting whole pages
        $query = ['type:' . DocumentFactory::SOLR_TYPE, 'uid:' . $item->getUid()];

        // delete document(s) from index, directly commit
        $solr->getWriteService()->deleteByQuery(implode(' AND ', $query));
    }

    /**
     * @param array $uidArray
     * @param Site $site
     * @param int $chunkSize
     *
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeByQueueEntriesAndSite(array $uidArray, Site $site, int $chunkSize = 1000)
    {
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

    /**
     * @return DocumentFactory
     */
    protected function getDocumentFactory(): DocumentFactory
    {
        return GeneralUtility::makeInstance(DocumentFactory::class);
    }

    /**
     * @return ConnectionManager
     */
    protected function getConnectionManager(): ConnectionManager
    {
        return GeneralUtility::makeInstance(ConnectionManager::class);
    }

    /**
     * @param ContextInterface $context
     * @return SolrConnection
     * @throws DBALDriverException
     * @throws NoSolrConnectionFoundException
     */
    protected function getSolrConnectionByContext(ContextInterface $context): SolrConnection
    {
        return $this->getConnectionManager()
            ->getConnectionByRootPageId($context->getSite()->getRootPageId(), $context->getLanguage());
    }

    /**
     * @param Site $site
     * @return SolrConnection[]
     */
    protected function getSolrConnectionsBySite(Site $site): array
    {
        $connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
        return $connectionManager->getConnectionsBySite($site);
    }

    /**
     * @return ItemRepository
     */
    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    /**
     * @return ItemGroupRepository
     */
    protected function getItemGroupRepository(): ItemGroupRepository
    {
        return GeneralUtility::makeInstance(ItemGroupRepository::class);
    }

    /**
     * @param File $file
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitIndexedFileToSolr(File $file)
    {
        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'indexedFileToSolr', [$file]);
    }

    /**
     * When merging is active and the root document of a mergeGroup is removed,
     * the mergeGroup needs to be re-added to make sure a new root document is determined.
     *
     * @param int $uid
     * @param Site $site
     * @throws ClientExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws FileDoesNotExistException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function indexNewRootDocumentWhenRequired(int $uid, Site $site)
    {
        $item = $this->getItemRepository()->findByUid($uid);
        if (is_null($item) || ! $this->getIsMergingEnabledFormItem($item)) {
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
}
