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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\Queue;

use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Scheduler\IndexingTask;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class InitializationAspectTest
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class IndexingTaskTest extends UnitTest
{
    /**
     * @return array
     */
    public static function getProgressDataProvider()
    {
        return [
            'All' => [100, 100, 0.0],
            'None' => [0, 100, 100.0],
            '50% done' => [50, 100, 50.0],
            '66% done' => [33333, 100000, 66.67],
            '33% done' => [66666, 100000, 33.33],
        ];
    }

    /**
     * @dataProvider getProgressDataProvider
     * @test
     * @param int $open
     * @param int $total
     * @param float $result
     */
    public function getProgressCalculatesCorrectPercentages($open, $total, $result)
    {
        /** @var \ApacheSolrForTypo3\Solrfal\Scheduler\IndexingTask $fixture */
        $fixture = $this->getAccessibleMock(IndexingTask::class, ['getItemRepository'], [], '', false);

        $persistenceMock = $this->getAccessibleMock(ItemRepository::class);
        $persistenceMock->expects(self::any())->method('count')->willReturn($total);
        $persistenceMock->expects(self::any())->method('countIndexingOutstanding')->willReturn($open);
        $fixture->expects(self::any())->method('getItemRepository')->willReturn($persistenceMock);

        self::assertEquals($result, $fixture->getProgress());
    }
}
