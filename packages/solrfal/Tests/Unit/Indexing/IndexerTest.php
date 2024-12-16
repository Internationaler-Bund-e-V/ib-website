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

use ApacheSolrForTypo3\Solrfal\Indexing\Indexer;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroupRepository;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class IndexerTest
 *
 * Timo Hund <timo.hund@dkd.de>
 */
class IndexerTest extends UnitTest
{
    /**
     * @test
     */
    public function testExceptionDuringIndexingDoesNotStopTheIndexingProcess()
    {
        $groupMock = $this->getDumbMock(ItemGroup::class);

        $itemGroupRepositoryMock = $this->getDumbMock(ItemGroupRepository::class);
        $itemGroupRepositoryMock->expects(self::once())->method('findByItem')->willReturn($groupMock);

        /** @var Indexer $indexer */
        $indexer = $this->getMockBuilder(Indexer::class)->setMethods(
            ['doesFileOfRootItemExist', 'addGroupDocumentsToIndex', 'getItemGroupRepository', 'markItemsInGroupAsFailed']
        )->getMock();
        $indexer->expects(self::once())->method('getItemGroupRepository')->willReturn($itemGroupRepositoryMock);
        $indexer->expects(self::once())->method('doesFileOfRootItemExist')->willReturn(true);
        $indexer->expects(self::once())->method('addGroupDocumentsToIndex')->will(self::throwException(new \Exception('someting went wrongs')));
        $indexer->expects(self::once())->method('markItemsInGroupAsFailed');

        $itemMock = $this->getDumbMock(Item::class);
        $result = $indexer->addToIndex($itemMock);
        self::assertFalse($result, 'Indexing should fail due to exceptions');
    }
}
