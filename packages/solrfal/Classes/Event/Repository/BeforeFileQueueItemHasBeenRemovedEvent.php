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

namespace ApacheSolrForTypo3\Solrfal\Event\Repository;

use ApacheSolrForTypo3\Solrfal\Queue\Item;

/**
 * Allows third party extensions to react on properties provided by this event.
 */
class BeforeFileQueueItemHasBeenRemovedEvent
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
