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

use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event listener for resource related events.
 */
class ResourceEventListener
{
    public function registerGeneratedPublicUrl(GeneratePublicUrlForResourceEvent $event): void
    {
        if (!(($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface)
            || !ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
        ) {
            return;
        }

        if (!isset($_SERVER['HTTP_X_TX_SOLR_IQ'])) {
            return;
        }

        /** @var PageContextDetectorFrontendIndexingAspect $frontendIndexingAspect */
        $frontendIndexingAspect = GeneralUtility::makeInstance(PageContextDetectorFrontendIndexingAspect::class);
        $frontendIndexingAspect->registerGeneratedPublicUrl($event->getResource());
    }
}
