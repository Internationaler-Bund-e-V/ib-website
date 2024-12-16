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

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\Statistic\QueueStatistic;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue as SolrIndexQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Queue extends SolrIndexQueue
{
    /**
     * @return ItemRepository
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    /**
     * Extracts the number of pending, indexed and erroneous items from the
     * Index Queue.
     *
     * @param Site $site
     * @param string $indexingConfigurationName
     *
     * @return QueueStatistic
     * @noinspection PhpUnused
     */
    public function getStatisticsBySite(Site $site, string $indexingConfigurationName = ''): QueueStatistic
    {
        $solrConfiguration = $site->getSolrConfiguration();
        $table = $solrConfiguration->getIndexQueueTableNameOrFallbackToConfigurationName($indexingConfigurationName);

        if ($table !== 'sys_file_storage') {
            return parent::getStatisticsBySite($site, $indexingConfigurationName);
        }

        return $this->getItemRepository()->getStatisticsBySite($site, $indexingConfigurationName);
    }
}
