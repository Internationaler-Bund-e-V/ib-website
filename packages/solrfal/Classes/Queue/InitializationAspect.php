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
use ApacheSolrForTypo3\Solrfal\Context\ContextFactory;
use ApacheSolrForTypo3\Solrfal\Detection\RecordDetectionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class InitializationAspect
 */
class InitializationAspect implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Post process Index Queue initialization
     *
     * @param Site $site The site to initialize
     * @param string $indexingConfigurationName The indexing configuration name, which be initialized
     * @param bool $indexQueueForConfigurationNameIsInitialized The status of given EXT:solr index queue initialization
     */
    public function postProcessIndexQueueInitialization(
        Site $site,
        string $indexingConfigurationName,
        ?bool $indexQueueForConfigurationNameIsInitialized = false,
    ): bool {
        $detectors = $this->getContextDetectorsForSite($site);
        $this->logger->info('Queue initialization triggered for site ' . $site->getSiteHash());
        $atLeastOneContextFailed = false;
        foreach ($detectors as $contextDetector) {
            $initialisationResult = $contextDetector->initializeQueue($indexingConfigurationName, $indexQueueForConfigurationNameIsInitialized);
            if (!$initialisationResult) {
                $this->logger->warning('Solr file index queue initialisation for "' . $indexingConfigurationName . '" failed');
                $atLeastOneContextFailed = true;
            }
        }
        return !$atLeastOneContextFailed;
    }

    /**
     * @return RecordDetectionInterface[]
     */
    protected function getContextDetectorsForSite(Site $site): array
    {
        return ContextFactory::getContextDetectors($site);
    }
}
