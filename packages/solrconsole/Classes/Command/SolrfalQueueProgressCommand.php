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

namespace ApacheSolrForTypo3\Solrconsole\Command;

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\Statistic\QueueStatistic;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The initialize command is responsible to initialize the index queue for a set of sites and index queue configurations.
 */
class SolrfalQueueProgressCommand extends SolrQueueProgressCommand
{
    /**
     * @var ?ItemRepository
     */
    protected ?ItemRepository $itemRepository = null;

    /**
     * @return ItemRepository
     */
    public function getItemRepository(): ItemRepository
    {
        if (!isset($this->itemRepository)) {
            $this->itemRepository = $this->itemRepository ?? GeneralUtility::makeInstance(ItemRepository::class);
        }
        return $this->itemRepository;
    }

    /**
     * @param ItemRepository $queueItemRepository
     */
    public function setItemRepository(ItemRepository $queueItemRepository)
    {
        $this->itemRepository = $queueItemRepository;
    }

    /**
     * @inheritDoc
     */
    protected function getStatisticsBySite(Site $site): QueueStatistic
    {
        return $this->getItemRepository()->getStatisticsBySite($site);
    }
}
