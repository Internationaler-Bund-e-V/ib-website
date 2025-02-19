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

use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ItemGroupRepository
 *
 * Repository class to retrieve a itemGroups from the database.
 */
class ItemGroupRepository
{
    /**
     * @return ItemGroup[]
     *
     * @throws DBALException
     */
    public function findAllIndexingOutStanding(int $limit, int $limitToSiteId = 0): array
    {
        $itemGroups = [];

        // merging is enabled we add all items with the same merge_id to the same merge set
        $mergeIdSets = $this->getItemRepository()->findAllOutStandingMergeIdSets($limit, $limitToSiteId);
        foreach ($mergeIdSets as $mergeId) {
            $itemGroups[] = $this->findByMergeId($mergeId);
        }

        return $itemGroups;
    }

    /**
     * @throws DBALException
     */
    public function findByItem(Item $item): ItemGroup
    {
        // when merging is enabled we build a group with all items of the same merge id
        return $this->findByMergeId($item->getMergeId());
    }

    /**
     * Retrieves a ItemGroup by the mergeId.
     *
     * @throws DBALException
     */
    public function findByMergeId(string $mergeId): ItemGroup
    {
        $itemsForMergeId = $this->getItemRepository()->findAllByMergeId($mergeId);
        $itemGroup = $this->getGroupWithItems($itemsForMergeId);
        $itemGroup->setGroupId($mergeId);

        return $itemGroup;
    }

    /**
     * @param Item[] $itemsForMergeId
     */
    private function getGroupWithItems(array $itemsForMergeId): ItemGroup
    {
        $group = new ItemGroup();
        foreach ($itemsForMergeId as $itemForMergeId) {
            $group->add($itemForMergeId);
        }
        return $group;
    }

    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }
}
