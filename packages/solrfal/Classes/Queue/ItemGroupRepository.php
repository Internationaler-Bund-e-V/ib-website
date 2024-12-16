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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ItemGroupRepository
 *
 * Repository class to retrieve a itemGroups from the database.
 */
class ItemGroupRepository
{

    /**
     * @param int $limit
     * @param int $limitToSiteId
     * @return ItemGroup[]
     */
    public function findAllIndexingOutStanding($limit, $limitToSiteId = 0): array
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
     * @param Item $item
     * @return ItemGroup
     */
    public function findByItem(Item $item): ItemGroup
    {
        // when merging is enabled we build a group with all items of the same merge id
        return $this->findByMergeId($item->getMergeId());
    }

    /**
     * Retrieves a ItemGroup by the mergeId.
     *
     * @param string $mergeId
     * @return ItemGroup
     */
    public function findByMergeId($mergeId): ItemGroup
    {
        $itemsForMergeId = $this->getItemRepository()->findAllByMergeId($mergeId);
        $itemGroup = $this->getGroupWithItems($itemsForMergeId);
        $itemGroup->setGroupId($mergeId);

        return $itemGroup;
    }

    /**
     * @param Item[] $itemsForMergeId
     * @return ItemGroup
     */
    private function getGroupWithItems(array $itemsForMergeId): ItemGroup
    {
        $group = new ItemGroup();
        foreach ($itemsForMergeId as $itemForMergeId) {
            $group->add($itemForMergeId);
        }
        return $group;
    }

    /**
     * @return ItemRepository
     */
    protected function getItemRepository(): ItemRepository
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ItemRepository::class);
    }
}
