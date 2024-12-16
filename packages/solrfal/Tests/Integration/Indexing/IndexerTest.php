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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Indexing;

use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solrfal\Indexing\Indexer;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use Solarium\Exception\HttpException;
use Throwable;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\FormProtection\Exception;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Indexer tests
 *
 * @author Markus Friedrich
 */
class IndexerTest extends IntegrationTest
{
    /**
     * The indexer
     *
     * @var Indexer
     */
    protected $indexer;

    protected function setUp(): void
    {
        $this->testExtensionsToLoad[] = 'typo3/sysext/filemetadata';

        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();

        $this->importExtTablesDefinition('fake_extension_table.sql');
        $GLOBALS['TCA']['tx_fakeextension_domain_model_news'] = include($this->getFixturePathByName('fake_extension_tca.php'));

        $this->indexer = GeneralUtility::makeInstance(Indexer::class);
    }

    protected function tearDown(): void
    {
        unset($this->indexer);
        parent::tearDown();
        $this->cleanUpSolrServerAndAssertEmpty();
    }

    /**
     * @test
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function canRemoveHugeAmountOfDocumentsByQueueUids()
    {
        $site = $this->getSite(1);
        $uidArray = range(1, 10240);

        try {
            $this->indexer->removeByQueueEntriesAndSite($uidArray, $site);
            $succeeded = true;
            $msg = '';
        } catch (HttpException|ImmediateResponseException $e) {
            $succeeded = false;
            $msg = 'Failed to remove documents from index: ' . print_r($e->getResponse(), true);
        }
        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function canRunIndexingTask()
    {
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->importDataSetFromFixture('run_indexing_task.xml');

        try {
            $this->indexer->processIndexQueue(1, false);
            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function threeSameFilesInAllContextCreateThreeSolrDocuments()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_notmerged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(3);

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function threeSameFilesInAllContextCreateOneSolrDocumentWithMerging()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(1);
            file_get_contents($this->getSolrConnectionUriAuthority() . '/solr/core_en/select?q=*:*');
            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function whenThreeItemsGetIndexedAndOneIsRemovedWithoutMergingTwoItemsAreLeft()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_notmerged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(3);

            $all = $this->getItemRepository()->findAll();
            $lastItem = array_pop($all);
            $this->indexer->removeFromIndex($lastItem);
            $this->waitToBeVisibleInSolr();

            // because we've removed one item, we expected that two will be left now
            $this->assertSolrContainsDocumentCount(2);

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function whenThreeItemsGetIndexedAndOneIsRemovedWithMergingOneItemIsLeft()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(1);

            $all = $this->getItemRepository()->findAll();
            $lastItem = array_pop($all);

            // remove from index is triggered with the consistency aspect
            $this->getItemRepository()->remove($lastItem);
            $this->waitToBeVisibleInSolr();

            // because we've removed one item, we expected that two will be left now
            $this->assertSolrContainsDocumentCount(1);

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function whenThreeItemsGetIndexedAndAllAreRemovedWithMergingNoItemIsLeft()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(1);

            $all = $this->getItemRepository()->findAll();
            foreach ($all as $item) {
                // remove from index is triggered with the consistency aspect
                $this->getItemRepository()->remove($item);
            }
            $this->waitToBeVisibleInSolr();

            //we've removed all items and assume now that no document is left
            $this->assertSolrIsEmpty();

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function whenThreeItemsGetIndexedAndAllAreRemovedInReversedOrderWithMergingNoItemIsLeft()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(1);

            $all = $this->getItemRepository()->findAll();
            $all = array_reverse($all);
            foreach ($all as $item) {
                // remove from index is triggered with the consistency aspect
                $this->getItemRepository()->remove($item);
            }
            $this->waitToBeVisibleInSolr();

            //we've removed all items and assume now that no document is left
            $this->assertSolrIsEmpty();

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function whenTreeItemsGetIndexedAndTheFirstAndLastAreDeletedTheSecondItemIsVisibleInSolr()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(1);

            $first = $this->getItemRepository()->findByUid(1);
            $this->getItemRepository()->remove($first);

            $last = $this->getItemRepository()->findByUid(3);
            $this->getItemRepository()->remove($last);

            $this->waitToBeVisibleInSolr();

            // one document (with uid 2) should be left
            $this->assertSolrContainsDocumentCount(1);

            // check the content in solr
            $solrContent = file_get_contents($this->getSolrConnectionUriAuthority() . '/solr/core_en/select?q=*:*');
            self::assertStringContainsString('"fileReferenceType":"tx_fakeextension_domain_model_news"', $solrContent, 'No news item in solr');

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function canIndexAllDocumentsWhenMergeIdIsMissingAndMergingIsDisabled()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_notmerged_and_nomergeid.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();
            $this->assertSolrContainsDocumentCount(3);

            $succeeded = true;
            $msg = '';
        } catch (Exception $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }
        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function canIndexFileInStorageContext(): void
    {
        $this->importDataSetFromFixture('index_file_in_storage_context.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        try {
            $this->indexer->processIndexQueue(1, false);
            $this->waitToBeVisibleInSolr();
            $this->assertSolrContainsDocumentCount(1);
        } catch (Exception $e) {
            self::fail('Failed to run indexing task: ' . $e->getMessage());
        }

        $solrContent = file_get_contents($this->getSolrConnectionUriAuthority() . '/solr/core_en/select?q=*:*');
        self::assertStringContainsString('"type":"tx_solr_file"', $solrContent, 'No tx_solr_file item in solr');
        self::assertStringContainsString('"filePublicUrl":"fileadmin/file9999.txt"', $solrContent, 'filePublicUrl invalid');
    }

    /**
     * @test
     */
    public function canIndexFileInStorageContextAndNonPublicStorage(): void
    {
        $this->importDataSetFromFixture('index_file_in_storage_context.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        // simulate non-public storage, to enfore dumpFile usage
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $connection->update('sys_file_storage', ['is_public' => 0], ['uid' => 1]);

        try {
            $this->indexer->processIndexQueue(1, false);
            $this->waitToBeVisibleInSolr();
            $this->assertSolrContainsDocumentCount(1);
        } catch (Exception $e) {
            self::fail('Failed to run indexing task: ' . $e->getMessage());
        }

        $solrContent = file_get_contents($this->getSolrConnectionUriAuthority() . '/solr/core_en/select?q=*:*');
        self::assertStringContainsString('"type":"tx_solr_file"', $solrContent, 'No tx_solr_file item in solr');
        self::assertStringContainsString('"filePublicUrl":"http://testone.site/en/index.php?eID=dumpFile&t=f&f=9999', $solrContent, 'filePublicUrl invalid');
    }

    /**
     * @test
     */
    public function deletionOfAllSiteItemsIsWorkingWithMerging()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();

            $this->assertSolrContainsDocumentCount(1);
            $site = $this->getSiteRepository()->getFirstAvailableSite();

            // removing from solr is handled by the signals
            $this->getItemRepository()->removeBySite($site);
            $this->waitToBeVisibleInSolr();

            // we assume that solr is empty because all documents of a site have been removed.
            $this->assertSolrIsEmpty();

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function deletionOfItemsByTypeIsWorkingWithMerging()
    {
        $this->importDataSetFromFixture('index_file_in_all_contexts_merged.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                pageContext = 1
                storageContext = 1
                recordContext = 1
                mergeDuplicates = 1
            }
            '
        );

        try {
            $this->indexer->processIndexQueue(3, false);
            $this->waitToBeVisibleInSolr();
            $this->assertSolrContainsDocumentCount(1);

            // removing from solr is handled by the signals
            $site = $this->getSiteRepository()->getFirstAvailableSite();

            $this->getItemRepository()->removeByTableAndUidInContext('record', $site, 'tx_fakeextension_domain_model_news', 1);
            $this->getItemRepository()->removeByTableAndUidInContext('page', $site, 'tt_content', 1);

            $this->waitToBeVisibleInSolr();
            $this->assertSolrContainsDocumentCount(1);

            // check the content in solr
            $solrContent = file_get_contents($this->getSolrConnectionUriAuthority() . '/solr/core_en/select?q=*:*');

            // we expect that only one document is left, which is the document from the storage context
            self::assertStringContainsString('"type":"tx_solr_file"', $solrContent, 'No tx_solr_file item in solr');

            $succeeded = true;
            $msg = '';
        } catch (Throwable $e) {
            $succeeded = false;
            $msg = 'Failed to run indexing task: ' . $e->getMessage();
        }

        self::assertTrue($succeeded, $msg);
    }

    /**
     * @test
     */
    public function indexingWithInvalidConnectionDoesNotSkipTheWholeIndexingProcess()
    {
        $this->importDataSetFromFixture('index_invalid_language_does_not_skip_indexing.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $this->indexer->processIndexQueue(3, false);
        $this->waitToBeVisibleInSolr();
        $this->assertSolrContainsDocumentCount(1);

        // check the content in solr
        $solrContent = file_get_contents($this->getSolrConnectionUriAuthority() . '/solr/core_en/select?q=*:*');

        // we expect that only one document is left, which is the document from the storage context
        self::assertStringContainsString('"type":"tx_solr_file"', $solrContent, 'No tx_solr_file item in solr');

        $failCount = $this->getItemRepository()->countFailures();
        $allCount = $this->getItemRepository()->count();
        $outStanding = $this->getItemRepository()->countIndexingOutstanding();

        self::assertSame(1, $failCount, 'One item with invalid language should be failed');
        self::assertSame(2, $allCount, 'We should have two items, one failed and one successful');
        self::assertSame(0, $outStanding, 'We should have no items, that are outstanding');
    }

    /**
     * @test
     */
    public function canIndexTwoSites()
    {
        $this->importDataSetFromFixture('index_two_sites.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $this->indexer->processIndexQueue(3, false);
        $this->waitToBeVisibleInSolr();
        $this->assertSolrContainsDocumentCount(2);
    }

    /**
     * @test
     */
    public function canLimitToSite()
    {
        $this->importDataSetFromFixture('index_two_sites.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        /** @var SiteRepository $siteRepository */
        $siteRepository = GeneralUtility::makeInstance(SiteRepository::class);
        $siteA = $siteRepository->getSiteByRootPageId(1);

        $this->indexer->processIndexQueue(3, false, $siteA);
        $this->waitToBeVisibleInSolr();
        $this->assertSolrContainsDocumentCount(1);
    }

    public function notExistentFileScenarios(): array
    {
        return [
            [
                'index_not_existing_in_fs_file_does_not_skip_whole_process.xml',
                [
                    'file9999.txt' => 'fileadmin',
                    // file8888.txt not imported in file system
                    'file7777.txt' => 'fileadmin',
                ],
                [
                    'indexingOutstandingBefore' => 3,
                    'documentsInSolr' => 2,
                    'indexingOutstandingAfter' => 0,
                    'errorredItems' => 1,
                ],
            ],
            [
                'index_not_existing_in_db_file_does_not_skip_whole_process.xml',
                [
                    'file9999.txt' => 'fileadmin',
                    // file8888.txt not imported in db
                    'file7777.txt' => 'fileadmin',
                ],
                [
                    'indexingOutstandingBefore' => 3,
                    'documentsInSolr' => 2,
                    'indexingOutstandingAfter' => 0,
                    'errorredItems' => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider notExistentFileScenarios
     * @test
     */
    public function indexingANotExistingFileDoesNotSkipTheWholeIndexingProcess($fixtureName, array $filesToImport, array $assertionsExpected)
    {
        $this->importDataSetFromFixture($fixtureName);
        foreach ($filesToImport as $fileName => $targetDirectory) {
            $this->placeTemporaryFile($fileName, $targetDirectory);
        }

        $countIndexingOutstanding = $this->getItemRepository()->countIndexingOutstanding();
        self::assertSame(
            $assertionsExpected['indexingOutstandingBefore'],
            $countIndexingOutstanding,
            'Can not proceed with indexing, because fixture is not imported successfully.'
        );

        try {
            $this->indexer->processIndexQueue(10, false);
        } catch (Throwable $exception) {
            self::fail(vsprintf(
                'Indexing process is interrupted. Uncaught exception is occurred in indexing process. %sException Code : %s %sMessage: %s %sPlease catch this exception on proper place.',
                [
                    PHP_EOL,
                    $exception->getCode(),
                    PHP_EOL,
                    $exception->getMessage(),
                    PHP_EOL,
                ]
            ));
        }

        $this->waitToBeVisibleInSolr();
        $this->assertSolrContainsDocumentCount($assertionsExpected['documentsInSolr']);

        $indexingOutstandingAfter = $this->getItemRepository()->countIndexingOutstanding();
        self::assertSame($assertionsExpected['indexingOutstandingAfter'], $indexingOutstandingAfter, 'Indexing process is interrupted. Failed item is in the queue again.');

        $errorredItems = $this->getItemRepository()->countFailures();
        self::assertSame($assertionsExpected['errorredItems'], $errorredItems, 'Indexing process is interrupted. Failed item is not marked as errored.');
    }
}
