<?php

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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Queue;

use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequeueItemHandlerTest
 */
class RequeueItemHandlerTest extends IntegrationTest
{

    /**
     * @throws NoSuchCacheException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
    }

    /**
     * @test
     */
    public function canMarkQueueItemAsNotIndexed()
    {
        $this->importDataSetFromFixture('can_mark_queue_item_as_not_indexed_on_requeue_action.xml');
        /* @var Queue $originalSolrQueue */
        $originalSolrQueue = GeneralUtility::makeInstance(Queue::class);
        /* @var ItemRepository $solrfalItemRepository */
        $solrfalItemRepository = GeneralUtility::makeInstance(ItemRepository::class);

        $beforeRequeue = $solrfalItemRepository->countIndexingOutstanding();
        self::assertEquals(0, $beforeRequeue, 'Can not proceed with test. Solrfal Queue should have 0 outstanding.');

        $originalSolrQueue->updateItem('optional_here_not_used', 2, time());
        $afterRequeueItem = $solrfalItemRepository->countIndexingOutstanding();
        self::assertSame(1, $afterRequeueItem, 'Can not requeue solrfal\'s queue item.');
    }
}
