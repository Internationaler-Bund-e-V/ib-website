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
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Allows third party extensions to replace or modify the file document
 * after the file info has been added to the document.
 *
 * Previously used with
 *   ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory::emitAddedSolrFileInformation()
 */
class AfterFileInfoHasBeenAddedToDocumentEvent
{
    public function __construct(
        private Document $document,
        private readonly Item $fileIndexQueueItem,
        private readonly Site $site,
        private readonly FileInterface $file,
    ) {}

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function overrideDocument(Document $document): void
    {
        $this->document = $document;
    }

    public function getFileIndexQueueItem(): Item
    {
        return $this->fileIndexQueueItem;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }
}
