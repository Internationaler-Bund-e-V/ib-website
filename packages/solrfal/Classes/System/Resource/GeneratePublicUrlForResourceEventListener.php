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

namespace ApacheSolrForTypo3\Solrfal\System\Resource;

use ApacheSolrForTypo3\Solr\IndexQueue\PageIndexerRequest;
use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;

/**
 * Listener for the GeneratePublicUrlForResourceEvent
 *
 * Listener is registering each file for that a public url is generated,
 * required for file detection in PageConext
 */
class GeneratePublicUrlForResourceEventListener
{
    /**
     * Determines the publicUrl if currently indexing and in a
     * non-public storage
     *
     * @param GeneratePublicUrlForResourceEvent $event
     * @throws AspectNotFoundException
     */
    public function __invoke(GeneratePublicUrlForResourceEvent &$event): void
    {
        if ($this->getServerRequest() === null
            || !$this->getServerRequest()->hasHeader(PageIndexerRequest::SOLR_INDEX_HEADER)
        ) {
            return;
        }

        PageContextDetectorFrontendIndexingAspect::registerGeneratedPublicUrl($event->getResource());
    }

    /**
     * Returns the ServerRequest
     *
     * @return ServerRequest|null
     */
    protected function getServerRequest(): ?ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
