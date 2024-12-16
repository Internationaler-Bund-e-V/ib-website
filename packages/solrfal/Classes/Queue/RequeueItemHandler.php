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

namespace ApacheSolrForTypo3\Solrfal\Queue;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequeueItemHandler
 */
class RequeueItemHandler
{
    /**
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * RequeueItemHandler constructor.
     */
    public function __construct()
    {
        $this->itemRepository = GeneralUtility::makeInstance(ItemRepository::class);
    }

    /**
     * Marks solrfal's index queue item as outstanding
     *
     * @param string $itemType
     * @param int $itemUid
     * @param int $updateCount
     * @return int
     * @noinspection PhpUnused Method is registered as hook, PHP-Intepreter does not recognize usage. See ext_tables.php
     * @noinspection PhpUnusedParameterInspection
     */
    public function postProcessIndexQueueUpdateItem(string $itemType, int $itemUid, int $updateCount): int
    {
        $itemToUpdate = $this->itemRepository->findByUid($itemUid);
        if (null === $itemToUpdate) {
            return $updateCount;
        }

        $updated = $this->itemRepository->markAsNotIndexed($itemToUpdate);
        return $updated + $updateCount;
    }
}
