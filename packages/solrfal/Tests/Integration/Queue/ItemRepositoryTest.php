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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Queue;

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Testcase for the ItemRepository class
 *
 * @author Timo Hund <timo.hund@dkd.de>
 * @author Markus Friedrich <markus.friedrich@dkd.de>
 */
class ItemRepositoryTest extends IntegrationTest
{
    /**
     * @var ItemRepository
     */
    protected $fileItemRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
        $this->fileItemRepository = GeneralUtility::makeInstance(ItemRepository::class);
        $resourceFactoryStub = $this->getMockBuilder(ResourceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getFileObject',
            ])->getMock();
        GeneralUtility::setSingletonInstance(ResourceFactory::class, $resourceFactoryStub);
        $resourceFactoryStub->expects(self::any())->method('getFileObject')->willReturnCallback([$this, 'getFileObject']);
    }

    /**
     * Returns mocks for FIle
     *
     * @return File|MockObject
     */
    public function getFileObject()
    {
        $args = func_get_args();
        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::any())->method('getUid')->willReturn($args[0]);
        return $fileMock;
    }

    /**
     * @test
     */
    public function canGetStatistic()
    {
        $this->importDataSetFromFixture('can_get_statistics.xml');
        $statistic = $this->fileItemRepository->getStatisticsByRootPageId(1);
        self::assertSame($statistic->getTotalCount(), 5, 'Can not get total count');
        self::assertSame($statistic->getFailedCount(), 2, 'Can not get failed count');
        self::assertSame($statistic->getPendingCount(), 1, 'Can not get pending count');
        self::assertSame($statistic->getSuccessCount(), 2, 'Can not get success count');
    }

    /**
     * @test
     */
    public function canGetStatisticForNonExistentRootPage()
    {
        $this->importDataSetFromFixture('can_get_statistics.xml');
        $statistic = $this->fileItemRepository->getStatisticsByRootPageId(5);
        self::assertSame($statistic->getTotalCount(), 0, 'Finds something, what does not exists');
        self::assertSame($statistic->getFailedCount(), 0, 'Finds something, what does not exists');
        self::assertSame($statistic->getPendingCount(), 0, 'Finds something, what does not exists');
        self::assertSame($statistic->getSuccessCount(), 0, 'Finds something, what does not exists');
    }

    /**
     * @test
     */
    public function findBy()
    {
        $this->importDataSetFromFixture('findBy.xml');
        $items = $this->fileItemRepository->findBy();

        self::assertCount(8, $items, 'Can not get 8 items for trying to delete items by Site and Context.');

        $siteMock = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock->expects(self::any())->method('getRootPageId')->willReturn(111);
        self::assertCount(3, $this->fileItemRepository->findBy([$siteMock]));
        self::assertCount(2, $this->fileItemRepository->findBy([], [], [], [2, 3]));
        self::assertCount(1, $this->fileItemRepository->findBy([], [], ['storage'], [2, 3]));
        self::assertCount(0, $this->fileItemRepository->findBy([], [], ['page'], [2, 3]));
    }

    /**
     * @test
     */
    public function findByFileReturnsRightItemObject()
    {
        $this->importDataSetFromFixture('can_find_by_file_object.xml');
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getUid',
            ])->getMock();
        $fileMock->expects(self::any())->method('getUid')->willReturn(2000);

        $items = $this->fileItemRepository->findByFile($fileMock);
        self::assertEquals(2, $items[0]->getUid());
        self::assertEquals(2000, $items[0]->getFile()->getUid());
    }

    /**
     * @test
     */
    public function findAllOutStandingMergeIdSetsIgnoresErroredItems()
    {
        $this->importDataSetFromFixture('can_find_indexing_outstanding_merged_but_not_errored.xml');
        $items = $this->fileItemRepository->findAllOutStandingMergeIdSets();
        self::assertCount(1, $items, 'Solrfals index queue does not recognize errored items and loops infinitely by working on queue.');
    }

    /**
     * @test
     */
    public function canFlushErrorsBySite()
    {
        $this->importDataSetFromFixture('can_flush_errors_by_site.xml');

        $siteMock = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock->expects(self::any())->method('getRootPageId')->willReturn(1);

        $allItems = $this->fileItemRepository->findAllOutStandingMergeIdSets();
        self::assertCount(3, $allItems, 'Can not proceed with test. Fixture should provide 3 outstanding queue items(and 2 errored).');

        $numberOfFlushedErrored = $this->fileItemRepository->flushErrorsBySite($siteMock);
        self::assertEquals(2, $numberOfFlushedErrored, 'Did not get the number of flushed errors.');
        $allItemsAfterErrorsFlush = $this->fileItemRepository->findAllOutStandingMergeIdSets();
        self::assertCount(5, $allItemsAfterErrorsFlush, 'Does not find outstanding items after flushing errored.');
    }

    /**
     * @test
     */
    public function countIndexingOutstandingReturnsRightNumber()
    {
        $this->importDataSetFromFixture('can_find_all_file_item_objects.xml');
        $itemsCount = $this->fileItemRepository->countIndexingOutstanding();
        self::assertSame(1, $itemsCount);
    }

    /**
     * @test
     */
    public function countFailuresReturnsRightNumber()
    {
        $this->importDataSetFromFixture('can_find_all_file_item_objects.xml');
        $itemsCount = $this->fileItemRepository->countFailures();
        self::assertEquals(2, $itemsCount);
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfItemAlreadyExists()
    {
        $this->importDataSetFromFixture('exists_finds_same_item.xml');

        // context_type = record
        $existingItem = $this->fileItemRepository->findByUid(3);
        self::assertTrue($this->fileItemRepository->exists($existingItem));

        // context_type = storage
        $existingItem = $this->fileItemRepository->findByUid(2);
        self::assertTrue($this->fileItemRepository->exists($existingItem));

        self::markTestIncomplete('This test does not notice all possible conditions. The Page context must be checked.');
        // @todo: https://github.com/TYPO3-Solr/ext-solrfal/issues/11
//        // context_type = page
//        $existingItem = $this->fileItemRepository->findByUid(x);
//        self::assertTrue($this->fileItemRepository->exists($existingItem));
    }

    /**
     * @test
     */
    public function findAllIndexingOutStandingReturnsRightItemList()
    {
        $this->importDataSetFromFixture('can_find_all_file_item_objects.xml');

        $items = $this->fileItemRepository->findAllIndexingOutStanding();

        self::assertCount(2, $items, 'ItemRepository::findAllIndexingOutStanding() returns wrong count of Items.');
        self::assertSame(1, $items[0]->getUid(), 'ItemRepository::findAllIndexingOutStanding() returns wrong Items.');
        self::assertSame(4, $items[1]->getUid(), 'ItemRepository::findAllIndexingOutStanding() returns wrong Items.');
    }

    /**
     * @test
     */
    public function markFileUpdatedChangesRightItem()
    {
        $this->importDataSetFromFixture('marks_right_item_as_updated.xml');
        $items = $this->fileItemRepository->findAllIndexingOutStanding();
        self::assertCount(2, $items, 'Can not get two outstanding items to mark one as updated.');

        $this->fileItemRepository->markFileUpdated(2000, ['context_type' => 'storage']);
        $itemsAfterUpdate = $this->fileItemRepository->findAllIndexingOutStanding();
        self::assertCount(3, $itemsAfterUpdate, 'Can not get three outstanding items after update one.');
        self::assertContains($this->fileItemRepository->findByUid(2), $itemsAfterUpdate);
    }

    /**
     * @test
     */
    public function removeByFileRemovesRightItems()
    {
        $this->importDataSetFromFixture('removeByFileRemovesRightItems.xml');
        $items = $this->fileItemRepository->findAll();
        self::assertCount(6, $items, 'Can not get 6 items for trying to delete items by file.');
        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::any())->method('getUid')->willReturn(3000);

        $this->fileItemRepository->removeByFile($fileMock);
        $itemsAfterDeleting = $this->fileItemRepository->findAll();
        self::assertCount(4, $itemsAfterDeleting, 'Can not get 4 items, which should exists after deleting two items from different sites.');
        self::assertEmpty($this->fileItemRepository->findByFile($fileMock), 'Items which must be deleted by File exist anyway.');
    }

    /**
     * @test
     */
    public function removeBySiteAndContextRemovesRightItems()
    {
        $this->importDataSetFromFixture('removeBySiteAndContextRemovesRightItems.xml');
        $items = $this->fileItemRepository->findAll();
        self::assertCount(8, $items, 'Can not get 8 items for trying to delete items by Site and Context.');
        $siteMock = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock->expects(self::any())->method('getRootPageId')->willReturn(111);

        $this->fileItemRepository->removeBySiteAndContext($siteMock, 'record');
        $itemsAfterRemovingFromSite2AndRecordContext = $this->fileItemRepository->findAll();
        self::assertCount(7, $itemsAfterRemovingFromSite2AndRecordContext, 'Can not get 7 items for trying to delete other items by Site and Context.');

        $this->fileItemRepository->removeBySiteAndContext($siteMock, 'page');
        $itemsAfterRemovingFromSite2AndPageContext = $this->fileItemRepository->findAll();
        self::assertCount(6, $itemsAfterRemovingFromSite2AndPageContext, 'Can not get 6 items for trying to delete other items by Site and Context.');
    }

    /**
     * @test
     */
    public function removeBy()
    {
        $this->importDataSetFromFixture('removeBy.xml');
        $items = $this->fileItemRepository->findAll();
        self::assertCount(8, $items, 'Can not get 8 items for trying to delete items by Site and Context.');
        $siteMock = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock->expects(self::any())->method('getRootPageId')->willReturn(111);

        // three items match that criterion
        self::assertSame(3, $this->fileItemRepository->countBy([$siteMock]));
        $this->fileItemRepository->removeBy([$siteMock]);

        $itemsAfterRemovingFromSite2AndPageContext = $this->fileItemRepository->findAll();
        self::assertCount(5, $itemsAfterRemovingFromSite2AndPageContext, 'Expected to have 5 items left after removing everything with site 2.');

        // two items match that criterion
        self::assertSame(2, $this->fileItemRepository->countBy([], [], [], [3, 4]));
        $this->fileItemRepository->removeBy([], [], [], [3, 4]);
        $itemsAfterRemovingFromSite2AndPageContext = $this->fileItemRepository->findAll();
        self::assertCount(3, $itemsAfterRemovingFromSite2AndPageContext, 'Expected to have 3 items left after removing everything with uid 3,4.');

        // 3 items are left in the database
        self::assertSame(3, $this->fileItemRepository->countBy());
        $this->fileItemRepository->removeBy();
        $itemsAfterRemovingFromSite2AndPageContext = $this->fileItemRepository->findAll();
        self::assertCount(0, $itemsAfterRemovingFromSite2AndPageContext, 'No item should be left when a removal without filter is triggered');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function removeByPageContextRemovesAllItems()
    {
        self::markTestIncomplete('This test does not notice all possible conditions. The Storage and Page context can not be tested.');
    }

    /**
     * @test
     */
    public function updateCanUpdateByItemUid()
    {
        $this->importDataSetFromFixture('update_can_update_by_itemUid.xml');
        $item = $this->fileItemRepository->findByUid(815);
        self::assertEquals(1461055660, $item->getLastUpdate());
        $this->fileItemRepository->update($item);
        $itemAfterUpdate = $this->fileItemRepository->findByUid(815);
        self::assertNotEquals(1461055660, $itemAfterUpdate->getLastUpdate());
    }

    /**
     * @test
     */
    public function purgeContextRemovesAllItemsForDesiredContext()
    {
        $this->importDataSetFromFixture('purgeContext_can_remove_all_items_with_desired_context.xml');
        // assert items for record context exist in fixture
        self::assertInstanceOf(Item::class, $this->fileItemRepository->findByUid(3), 'Can not find required Item defined in Fixture.');
        self::assertInstanceOf(Item::class, $this->fileItemRepository->findByUid(815), 'Can not find required Item 815 defined in Fixture.');

        $this->fileItemRepository->purgeContext('record');
        self::assertNull($this->fileItemRepository->findByUid(3), 'Item with Uid=3 and context_type=record still exists after purging record context.');
        self::assertNull($this->fileItemRepository->findByUid(815), 'Item with Uid=815 and context_type=record still exists after purging record context.');
    }

    /**
     * @test
     */
    public function canRemoveByTableInRecordContext()
    {
        $this->importDataSetFromFixture('canRemoveByTableInRecordContext.xml');
        self::assertCount(8, $this->fileItemRepository->findAll(), 'Can not proceed the test without all 8 Items in Solrfal::ItemRepository.');

        $siteMock1 = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock1->expects(self::any())->method('getRootPageId')->willReturn(1);
        $this->fileItemRepository->removeByTableInRecordContext($siteMock1, 'first_phantom_table');
        self::assertNull($this->fileItemRepository->findByUid(1), 'Solrfals ItemRepository::removeByTableInRecordContext() did not remove the Item from Site=1 and first_phantom_table as expected. Item Uid=1 should be removed.');
        self::assertNull($this->fileItemRepository->findByUid(3), 'Solrfals ItemRepository::removeByTableInRecordContext() did not remove the Item from Site=1 and first_phantom_table as expected. Item Uid=3 should be removed.');
        self::assertCount(6, $this->fileItemRepository->findAll(), 'Solrfals ItemRepository::removeByTableInRecordContext() removed more Items than was expected.');

        $siteMock2 = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock2->expects(self::any())->method('getRootPageId')->willReturn(111);
        $this->fileItemRepository->removeByTableInRecordContext($siteMock2, 'phantom_table');
        self::assertNull($this->fileItemRepository->findByUid(201), 'Solrfals ItemRepository::removeByTableInRecordContext() did not remove the Item from Site=2 and phantom_table as expected. Item Uid=201 should be removed.');
        self::assertCount(5, $this->fileItemRepository->findAll(), 'Solrfals ItemRepository::removeByTableInRecordContext() removed more Items than was expected.');
    }

    /**
     * @test
     */
    public function canRemoveByIndexingConfigurationInRecordContext()
    {
        $this->importDataSetFromFixture('canRemoveByIndexingConfigurationInRecordContext.xml');
        self::assertCount(8, $this->fileItemRepository->findAll(), 'Can not proceed the test without all 8 Items in Solrfal::ItemRepository.');

        $siteMock1 = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])->getMock();
        $siteMock1->expects(self::any())->method('getRootPageId')->willReturn(1);
        $this->fileItemRepository->removeByIndexingConfigurationInRecordContext($siteMock1, 'news');
        self::assertNull($this->fileItemRepository->findByUid(1), 'Solrfals ItemRepository::removeByIndexingConfigurationInRecordContext() did not remove the Item from Site=1 and first_phantom_table as expected. Item Uid=1 should be removed.');
        self::assertNull($this->fileItemRepository->findByUid(3), 'Solrfals ItemRepository::removeByIndexingConfigurationInRecordContext() did not remove the Item from Site=1 and first_phantom_table as expected. Item Uid=3 should be removed.');
        self::assertCount(6, $this->fileItemRepository->findAll(), 'Solrfals ItemRepository::removeByIndexingConfigurationInRecordContext() removed more Items than was expected.');

        $siteMock2 = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRootPageId',
            ])
            ->getMock();
        $siteMock2->expects(self::any())->method('getRootPageId')->willReturn(111);
        $this->fileItemRepository->removeByIndexingConfigurationInRecordContext($siteMock2, 'phantom_extension');
        self::assertNull($this->fileItemRepository->findByUid(201), 'Solrfals ItemRepository::removeByIndexingConfigurationInRecordContext() did not remove the Item from Site=2 and phantom_table as expected. Item Uid=201 should be removed.');
        self::assertCount(5, $this->fileItemRepository->findAll(), 'Solrfals ItemRepository::removeByIndexingConfigurationInRecordContext() removed more Items than was expected.');
    }
}
