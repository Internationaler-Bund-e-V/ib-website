<?php

/** @noinspection PhpUnhandledExceptionInspection */

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

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrfal\Detection\StorageContextDetector;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Storage context detector tests
 *
 * @author Markus Friedrich
 */
class StorageContextDetectorTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing {
                storageContext = 1
                storageContext.1 {
                    fileExtensions = txt,pdf
                }
            }
            '
        );
    }

    /**
     * Tests the file detection during index queue initialization, without configured folders
     *
     * Testing the detection without folder definitions ensures the back-words compatibility, since
     * older installations may not have definitions for valid or exclude folders
     *
     * @test
     */
    public function detectFilesDuringInitializationWithoutFolderDefinitions()
    {
        $this->importDataSetFromFixture('StorageContext/detects_files_during_initialization_without_folder_definition.xml');
        $this->placeTemporaryFile('file7777.gif', 'fileadmin');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin/exclude');
        $this->placeTemporaryFile('file7777.gif', 'fileadmin/exclude/exclude_sub');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin/exclude/exclude_sub');

        $site = $this->getSiteRepository()->getFirstAvailableSite();
        $this->getStorageContextDetector($site)->initializeQueue(['fileadmin' => true]);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();

        self::assertCount(3, $indexQueueContent, 'Number of file index queue entries is not as expected');
        $filesInQueue = [];
        foreach ($indexQueueContent as $queueItem) {
            $filesInQueue[] = $queueItem->getFile()->getUid();
        }
        self::assertTrue(in_array(8888, $filesInQueue), 'File 8888 not detected');
        self::assertTrue(in_array(88881, $filesInQueue), 'File 88881 not detected');
        self::assertTrue(in_array(99991, $filesInQueue), 'File 99991 not detected');

        $this->removeTemporaryFiles();
    }

    /**
     * @param array $indexQueueContent
     * @param int $uid
     */
    protected function assertIndexQueueContainsFileUid(array $indexQueueContent, int $uid)
    {
        $isInQueue = false;
        foreach ($indexQueueContent as $indexQueueItem) {
            if ($indexQueueItem->getFile()->getUid() === $uid) {
                $isInQueue = true;
            }
        }

        self::assertTrue($isInQueue, 'Asserting that the is an item in the queue for uid ' . $uid);
    }

    /**
     * Tests the file detection during index queue initialization
     *
     * @test
     */
    public function detectFilesDuringInitializationWithExcludedFolderDefinitions()
    {
        $this->importDataSetFromFixture('StorageContext/detects_files_during_initialization.xml');
        $this->placeTemporaryFile('file7777.gif', 'fileadmin');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin/exclude');
        $this->placeTemporaryFile('file7777.gif', 'fileadmin/exclude/exclude_sub');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin/exclude/exclude_sub');

        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing.storageContext.1 {
                folders = *
                excludeFolders = exclude
            }
            '
        );

        $site = $this->getSiteRepository()->getFirstAvailableSite();
        $this->getStorageContextDetector($site)->initializeQueue(['fileadmin' => true]);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'Number of file index queue entries is not as expected');
        self::assertEquals(8888, $indexQueueContent[0]->getFile()->getUid(), 'Wrong file detected');

        $this->removeTemporaryFiles();
    }

    /**
     * Returns the storage context detector
     *
     * @param Site $site
     * @return StorageContextDetector
     */
    protected function getStorageContextDetector(Site $site): StorageContextDetector
    {
        return GeneralUtility::makeInstance(StorageContextDetector::class, $site);
    }

    /**
     * @test
     */
    public function initializeStorageContext()
    {
        $this->importDataSetFromFixture('detects_files_in_storage_context.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $site = $this->getSite(1);
        $storageContextDetector = $this->getStorageContextDetector($site);
        $storageContextDetector->initializeQueue(['fileadmin' => true]);

        self::assertEquals(
            2,
            $this->getItemRepository()->getStatisticsBySite($site, 'fileadmin')->getTotalCount(),
            'Number of file index queue entries is not as expected (fileadmin)'
        );
        self::assertEquals(
            1,
            $this->getItemRepository()->getStatisticsBySite($site, 'fileadmin2')->getTotalCount(),
            'Number of file index queue entries is not as expected (fileadmin2)'
        );
        self::assertCount(
            3,
            $this->getItemRepository()->findAll(),
            'Total number of file index queue entries is not as expected'
        );

        $this->removeTemporaryFiles();
    }
}
