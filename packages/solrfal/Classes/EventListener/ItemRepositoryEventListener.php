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

namespace ApacheSolrForTypo3\Solrfal\EventListener;

use ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeFileQueueItemHasBeenRemovedEvent;
use ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeMultipleFileQueueItemsHaveBeenRemovedEvent;
use ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect;
use Throwable;

class ItemRepositoryEventListener
{
    private ConsistencyAspect $consistencyAspect;

    public function __construct(ConsistencyAspect $consistencyAspect)
    {
        $this->consistencyAspect = $consistencyAspect;
    }

    public function removeSolrEntryForItem(BeforeFileQueueItemHasBeenRemovedEvent $event): void
    {
        try {
            $this->consistencyAspect->removeSolrEntryForItem($event->getItem());
        } catch (Throwable) {
        }
    }

    public function removeMultipleQueueItemsFromSolr(BeforeMultipleFileQueueItemsHaveBeenRemovedEvent $event): void
    {
        try {
            $this->consistencyAspect->removeMultipleQueueItemsFromSolr($event->getItemUids());
        } catch (Throwable) {
        }
    }
}
