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
use ApacheSolrForTypo3\Solrfal\Queue\Item;

/**
 * Allows third party extensions to replace or modify the file metadata
 * after it is retrieved and before its fields are added to the document.
 *
 * Note: The getters for Document and Item return the clones of objects,
 *       to prevent from side effects.
 *       Please use other Events to make changes on them.
 *
 * Previously used with
 *   ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory::emitFileMetaDataRetrieved()
 *   Now you can override the data with {@link AfterFileMetaDataHasBeenRetrievedEvent::overrideMetaData()} method.
 */
class AfterFileMetaDataHasBeenRetrievedEvent
{
    /**
     * @param array<string, string|int|bool|null> $metaData
     * @param Document $document The Document object
     * @param Item $fileIndexQueueItem The file index queue object
     */
    public function __construct(
        private array $metaData,
        private readonly Document $document,
        private readonly Item $fileIndexQueueItem,
    ) {}

    /**
     * @return array<string, string|int|bool|null>
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * @param array<string, string|int|bool|null> $metaData
     */
    public function overrideMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function getDocument(): Document
    {
        return clone $this->document;
    }

    public function getFileIndexQueueItem(): Item
    {
        return clone $this->fileIndexQueueItem;
    }
}
