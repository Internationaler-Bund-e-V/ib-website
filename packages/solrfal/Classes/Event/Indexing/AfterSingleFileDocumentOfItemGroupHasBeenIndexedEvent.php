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

namespace ApacheSolrForTypo3\Solrfal\Event\Indexing;

use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;

/**
 * Allows third party extensions to react on properties provided by this event.
 *
 * Note: This event can not be used to modify provided properties.
 */
class AfterSingleFileDocumentOfItemGroupHasBeenIndexedEvent
{
    public function __construct(
        private readonly Document $document,
        private readonly ItemGroup $itemGroup,
    ) {}

    public function getDocument(): Document
    {
        return clone $this->document;
    }

    public function getItemGroup(): ItemGroup
    {
        return clone $this->itemGroup;
    }
}
