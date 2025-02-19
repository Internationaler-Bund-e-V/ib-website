<?php

declare(strict_types=1);

/**
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

namespace ApacheSolrForTypo3\Solrfal\EventListener;

use ApacheSolrForTypo3\Solr\Event\IndexQueue\AfterIndexQueueItemHasBeenMarkedForReindexingEvent;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterIndexQueueItemHasBeenMarkedForReindexing
{
    protected ItemRepository $itemRepository;

    /**
     * @throws DBALException
     */
    public function __invoke(AfterIndexQueueItemHasBeenMarkedForReindexingEvent $event): void
    {
        $result = $this->postProcessIndexQueueUpdateItem(
            $event->getItemUid(),
            $event->getUpdateCount()
        );
        $event->setUpdateCount($result);
    }

    /**
     * RequeueItemHandler constructor.
     */
    public function __construct(?ItemRepository $itemRepository = null)
    {
        $this->itemRepository = $itemRepository ?? GeneralUtility::makeInstance(ItemRepository::class);
    }

    /**
     * Marks EXT:solrfal's index queue item as outstanding
     *
     * @throws DBALException
     * @todo: The method implementation is most probably wrong, because provided EXT:solr.queue.itemUid belongs to the record,
     *        but EXT:solrfal.queue.uid does not belong to anything. So `->findByUid()` is not right there.
     */
    public function postProcessIndexQueueUpdateItem(
        int|string $itemUid,
        int $updateCount
    ): int {
        $itemToUpdate = $this->itemRepository->findByUid($itemUid);
        if ($itemToUpdate === null) {
            return $updateCount;
        }

        $updated = $this->itemRepository->markAsNotIndexed($itemToUpdate);
        return $updated + $updateCount;
    }
}
