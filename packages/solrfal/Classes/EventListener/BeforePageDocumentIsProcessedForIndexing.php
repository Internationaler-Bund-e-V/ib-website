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

namespace ApacheSolrForTypo3\Solrfal\EventListener;

use ApacheSolrForTypo3\Solr\Event\Indexing\BeforePageDocumentIsProcessedForIndexingEvent;
use ApacheSolrForTypo3\Solr\Exception\InvalidArgumentException;
use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect;
use ApacheSolrForTypo3\Solrfal\Exception\Detection\InvalidHookException as InvalidDetectionHookException;
use ApacheSolrForTypo3\Solrfal\Exception\Service\InvalidHookException as InvalidServiceHookException;
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BeforePageDocumentIsProcessedForIndexing is responsible to trigger the detection of attachments
 * in EXT:solrfals page context.
 *
 * Previously triggered via:
 *   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['Indexer']['indexPagePostProcessPageDocument']
 *   and/or
 *   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['IndexQueueIndexer']['preAddModifyDocuments']
 */
class BeforePageDocumentIsProcessedForIndexing
{
    /**
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws InvalidDetectionHookException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws UnknownLinkHandlerException
     */
    public function __invoke(BeforePageDocumentIsProcessedForIndexingEvent $event): void
    {
        GeneralUtility::makeInstance(PageContextDetectorFrontendIndexingAspect::class)
            ->postProcessPageDocument(
                $event->getDocument(),
                $event->getTsfe(),
            );
    }
}
