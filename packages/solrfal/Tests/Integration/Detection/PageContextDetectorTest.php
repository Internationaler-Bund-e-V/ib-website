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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Detection;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Index\IndexService;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorFrontendIndexingAspect;
use ApacheSolrForTypo3\Solrfal\Queue\Queue;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use Doctrine\DBAL\DBALException;
use ReflectionMethod;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Exception as TestingFrameworkCoreException;

/**
 * Page context detector tests
 *
 * @author Markus Friedrich
 */
class PageContextDetectorTest extends IntegrationTest
{
    /**
     * @throws NoSuchCacheException
     * @throws DBALException
     * @throws TestingFrameworkCoreException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
    }

    /**
     * @test
     */
    public function canDetectContentDeletion()
    {
        $this->importDataSetFromFixture('PageContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start([], ['tt_content' => [10 => ['delete' => 1]]]);
        $dataHandler->process_cmdmap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(1, count($indexQueueContent), 'Index queue not updated as expected, deletion of sys_file_reference ignored.');
        self::assertEquals(8888, $indexQueueContent[0]->getFile()->getUid(), 'Remaining file in index queue is not "file8888.pdf" as expected.');
    }

    /**
     * @test
     */
    public function detectRelationDeletion()
    {
        $this->importDataSetFromFixture('PageContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start([], ['sys_file_reference' => [9999 => ['delete' => 1]]]);
        $dataHandler->process_cmdmap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(1, count($indexQueueContent), 'Relation deletion ignored, file "file9999.txt" is still in queue.');
        self::assertEquals(8888, $indexQueueContent[0]->getFile()->getUid(), 'Remaining file in index queue is not "file8888.pdf" as expected.');
    }

    /**
     * @test
     */
    public function detectPageDeletion()
    {
        $this->importDataSetFromFixture('PageContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start([], ['pages' => [2 => ['delete' => 1]]]);
        $dataHandler->process_cmdmap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(0, count($indexQueueContent), 'Page deletion didn\'t trigger the file deletion.');
    }

    /**
     * @test
     */
    public function detectContentHiding()
    {
        $this->importDataSetFromFixture('PageContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start(['tt_content' => [10 => ['hidden' => 1]]], []);
        $dataHandler->process_datamap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(1, count($indexQueueContent), 'Index queue not updated as expected, hiding of tt_content record ignored.');
        self::assertEquals(8888, $indexQueueContent[0]->getFile()->getUid(), 'Remaining file in index queue is not "file8888.pdf" as expected.');
    }

    /**
     * @test
     */
    public function detectRelationHiding()
    {
        $this->importDataSetFromFixture('PageContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start(['sys_file_reference' => [9999 => ['hidden' => 1]]], []);
        $dataHandler->process_datamap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(1, count($indexQueueContent), 'Relation hiding ignored, file "file9999.txt" is still in queue.');
        self::assertEquals(8888, $indexQueueContent[0]->getFile()->getUid(), 'Remaining file in index queue is not "file8888.pdf" as expected.');
    }

    /**
     * @test
     */
    public function detectPageHiding()
    {
        $this->importDataSetFromFixture('PageContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start(['pages' => [2 => ['hidden' => 1]]], []);
        $dataHandler->process_datamap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(0, count($indexQueueContent), 'Page hidding didn\'t trigger the file deletion.');
    }

    /**
     * This testcase checks if we can create a new test-page on the root level without any errors.
     *
     * @test
     */
    public function canCreateSiteOneRootLevel()
    {
        $this->importDataSetFromFixture('PageContext/observes_create.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $this->assertSolrQueueContainsAmountOfItems(0);
        $dataHandler = $this->getDataHandler();
        $dataHandler->start(
            [
                'pages' => [
                    'NEW' => [
                        'hidden' => 0,
                        'pid' => 0,
                        'doktype' => 1,
                        'slug' => '',
                    ],
                ],
            ],
            []
        );
        $dataHandler->process_datamap();

        // the item is outside a siteroot, so we should not have any queue entry
        $this->assertSolrQueueContainsAmountOfItems(0);
    }

    /**
     * This testcase checks if we can create a new testpage on the root level without any errors.
     *
     * @test
     */
    public function canCreateSubPageBelowSiteRoot()
    {
        $this->importDataSetFromFixture('PageContext/observes_create.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $this->assertSolrQueueContainsAmountOfItems(0);
        $dataHandler = $this->getDataHandler();
        $dataHandler->start(['pages' => ['NEW' => ['hidden' => 0, 'pid' => 1]]], []);
        $dataHandler->process_datamap();

        // we should have one item in the solr queue
        $this->assertSolrQueueContainsAmountOfItems(1);
    }

    /**
     * Checks if files attached to access protected content elements can be handled
     *
     * Note:
     *   The TypoScript setting:
     *     `plugin.tx_solr.index.enableFileIndexing.pageContext.enableFields.accessGroups >`
     *   is not picked up from fixture.
     *   See: https://github.com/TYPO3-Solr/ext-solrfal/blob/1b8c6d2edf4153b6014ee7840eee12f9179b1c3b/Tests/Integration/Detection/Fixtures/PageContext/handles_access_protected_content_elements.xml#L38
     *
     * @test
     */
    public function canHandleAccessProtectedContentElements()
    {
        $this->importDataSetFromFixture('PageContext/handles_access_protected_content_elements.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $methodAddDetectedFiles = new ReflectionMethod(PageContextDetectorFrontendIndexingAspect::class, 'addDetectedFilesToPage');
        $methodAddDetectedFiles->setAccessible(true);

        $this->getDataHandler();
        $fakeTSFE = $this->fakeTSFE(2, [0, -2, 0]);

        $pageContextDetectorFrontendIndexingAspect = $this->getPageContextDetectorFrontendIndexingAspect([8888], [10 => BackendUtility::getRecord('tt_content', 10)]);
        self::assertSame(0, $this->getItemRepository()->count(), 'File Index Queue is not empty as expected.');
        $pageAccessRootline = GeneralUtility::makeInstance(Rootline::class, 'c:0');
        $methodAddDetectedFiles->invokeArgs($pageContextDetectorFrontendIndexingAspect, [$fakeTSFE, $pageAccessRootline]);
        self::assertSame(1, $this->getItemRepository()->count(), 'PageContextDetector didn\'t detect exactly 1 document as expected.');

        // simulate indexing of access protected contents
        $fakeTSFE = $this->fakeTSFE(2, [0, -2, 1]);
        $pageContextDetectorFrontendIndexingAspect = $this->getPageContextDetectorFrontendIndexingAspect([9999], [20 => BackendUtility::getRecord('tt_content', 20)]);
        self::assertSame(1, $this->getItemRepository()->count(), 'File Index Queue didn\'t contain the document detected in last run.');
        $pageAccessRootline = GeneralUtility::makeInstance(Rootline::class, 'c:1');
        $methodAddDetectedFiles->invokeArgs($pageContextDetectorFrontendIndexingAspect, [$fakeTSFE, $pageAccessRootline]);
        self::assertSame(2, $this->getItemRepository()->count(), 'File Index Queue didn\'t contain the 2 documents from the two detection runs.');
    }

    /**
     * Checks if additional file uids can be added with a hook.
     *
     * @test
     */
    public function canAddAdditionalForcedFileUids()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['PageContextDetectorAspectInterface'][] = TestAspect::class;

        $this->importDataSetFromFixture('PageContext/handles_add_additional_files.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file11111.txt', 'fileadmin');

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(Tsfe::class)->getTsfeByPageIdAndLanguageId(2, 0);

        $methodAddDetectedFiles = new ReflectionMethod(PageContextDetectorFrontendIndexingAspect::class, 'addDetectedFilesToPage');
        $methodAddDetectedFiles->setAccessible(true);

        // simulate indexing of public contens
        $GLOBALS['TSFE']->gr_list = '0,-2,0';
        $pageContextDetectorFrontendIndexingAspect = $this->getPageContextDetectorFrontendIndexingAspect([8888], [10 => BackendUtility::getRecord('tt_content', 10)]);
        self::assertSame(0, $this->getItemRepository()->count(), 'File Index Queue is not empty as expected.');
        $pageAccessRootline = GeneralUtility::makeInstance(Rootline::class, 'c:0');
        $methodAddDetectedFiles->invokeArgs($pageContextDetectorFrontendIndexingAspect, [$GLOBALS['TSFE'], $pageAccessRootline]);

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEquals(2, count($indexQueueContent), 'Relation hiding ignored, file "file9999.txt" is still in queue.');
        self::assertEquals(8888, $indexQueueContent[0]->getFile()->getUid(), 'Remaining file in index queue is not "file8888.pdf" as expected.');
        self::assertEquals(11111, $indexQueueContent[1]->getFile()->getUid(), 'Remaining file in index queue is not "file11111.txt" as expected.');

        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['PageContextDetectorAspectInterface']);
    }

    /**
     * Checks if detected files were resetted after item indexing
     *
     * @test
     */
    public function canResetDetectedFileUids()
    {
        $this->importDataSetFromFixture('PageContext/handles_access_protected_content_elements.xml');
        $registry = GeneralUtility::makeInstance(Registry::class);
        $registry->set('tx_solrfal', 'pageContextDetector.successfulFileUids', [1, 2, 3]);

        $site = $this->getSiteRepository()->getSiteByRootPageId(1);
        $indexService = GeneralUtility::makeInstance(IndexService::class, $site);
        $methodEmitSignal = new ReflectionMethod(IndexService::class, 'emitSignal');
        $methodEmitSignal->setAccessible(true);
        $methodEmitSignal->invokeArgs($indexService, ['afterIndexItem', []]);

        $successfulFileUids = $registry->get('tx_solrfal', 'pageContextDetector.successfulFileUids');
        self::assertNull($successfulFileUids, 'Detected file uids were not resetted by using signal "afterIndexItem".');
    }

    /**
     * @param int $assertedItemCount
     */
    protected function assertSolrQueueContainsAmountOfItems($assertedItemCount)
    {
        /** @var $indexQueue Queue */
        $indexQueue = GeneralUtility::makeInstance(Queue::class);
        self::assertSame($assertedItemCount, $indexQueue->getAllItemsCount(), 'EXT:solr index queue does not contain expected item amount');
    }

    /**
     * Returns the PageContextDetectorFrontendIndexingAspect
     *
     * @param array $collectedFileUids
     * @param array $collectedContentElements
     * @return PageContextDetectorFrontendIndexingAspect
     */
    protected function getPageContextDetectorFrontendIndexingAspect(array $collectedFileUids, array $collectedContentElements)
    {
        /* @var PageContextDetectorFrontendIndexingAspect $pageContextDetectorFrontendIndexingAspect */
        $pageContextDetectorFrontendIndexingAspect = GeneralUtility::makeInstance(PageContextDetectorFrontendIndexingAspect::class);
        $pageContextDetectorFrontendIndexingAspect::$collectedFileUids = $collectedFileUids;
        $pageContextDetectorFrontendIndexingAspect::$collectedContentElements = $collectedContentElements;
        return $pageContextDetectorFrontendIndexingAspect;
    }
}
