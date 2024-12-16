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

namespace ApacheSolrForTypo3\Solrfal\Queue;

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\IndexQueue\InitializationPostProcessor;
use ApacheSolrForTypo3\Solrfal\Context\ContextFactory;
use ApacheSolrForTypo3\Solrfal\Detection\RecordDetectionInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InitializationAspect
 */
class InitializationAspect implements InitializationPostProcessor
{
    /**
     * Post process Index Queue initialization
     *
     * @param Site $site The site to initialize
     * @param array $indexingConfigurations Initialized indexing configurations
     * @param array $initializationStatus Results of Index Queue initializations
     */
    public function postProcessIndexQueueInitialization(Site $site, array $indexingConfigurations, array $initializationStatus)
    {
        $detectors = $this->getContextDetectorsForSite($site);
        $this->getLogger()->info('Queue initialization triggered for site ' . $site->getSiteHash());
        foreach ($detectors as $contextDetector) {
            $contextDetector->initializeQueue($initializationStatus);
        }
    }

    /**
     * @param Site $site
     * @return RecordDetectionInterface[]
     */
    protected function getContextDetectorsForSite(Site $site): array
    {
        return ContextFactory::getContextDetectors($site);
    }

    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }
}
