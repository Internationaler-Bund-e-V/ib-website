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

/**
 * Allows third party extensions to react on properties provided by this event.
 */
class AfterMultipleFileQueueItemsHaveBeenRemovedEvent
{
    /**
     * @var array<int>
     */
    protected array $itemUids;

    /**
     * @param array<int> $itemUids
     */
    public function __construct(array $itemUids)
    {
        $this->itemUids = $itemUids;
    }

    /**
     * @return array<int>
     */
    public function getItemUids(): array
    {
        return $this->itemUids;
    }
}
