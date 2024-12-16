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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Domain\Repository;

use ApacheSolrForTypo3\Solrfal\Domain\Model\ReferenceIndexEntry;
use ApacheSolrForTypo3\Solrfal\Domain\Repository\ReferenceIndexEntryRepository;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for the ReferenceIndexEntryRepository class
 */
class ReferenceIndexEntryRepositoryTest extends IntegrationTest
{
    /**
     * @var ReferenceIndexEntryRepository
     */
    protected $referenceIndexEntryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->referenceIndexEntryRepository = GeneralUtility::makeInstance(ReferenceIndexEntryRepository::class);
    }

    /**
     * @test
     */
    public function findByReferenceRecordReturnsRightReferenceIndexEntryCollection()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntries = $this->referenceIndexEntryRepository->findByReferenceRecord('sys_file', 47);
        self::assertCount(6, $referenceIndexEntries, 'Finds more ReferenceIndexEntries as expected 6 items.');
    }

    /**
     * @test
     */
    public function findByReferenceRecordCanExcludeTables()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntries = $this->referenceIndexEntryRepository->findByReferenceRecord('sys_file', 47, ['sys_file_metadata']);
        self::assertCount(5, $referenceIndexEntries, 'Finds more ReferenceIndexEntries as expected 5 items by excluding tablename sys_file_metadata.');
    }

    /**
     * @test
     */
    public function findByReferenceRecordCanLimitToTables()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntries = $this->referenceIndexEntryRepository->findByReferenceRecord('sys_file', 47, [], ['sys_file_metadata']);
        self::assertCount(1, $referenceIndexEntries, 'Finds more ReferenceIndexEntries as expected 1 items by limiting tablename to sys_file_reference.');
    }

    /**
     * @test
     */
    public function findByReferenceRecordCanIgnoreDeletedReferenceIndexRecords()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntries = $this->referenceIndexEntryRepository->findByReferenceRecord('sys_file', 87, [], ['pages', 'tt_content', 'sys_file_reference']);
        self::assertCount(0, $referenceIndexEntries, 'ReferenceIndexEntryRepository::findByReferenceRecord() finds deleted ReferenceIndexEntries, which MUST NOT be found.');
    }

    /**
     * @test
     */
    public function findOneByReferenceRecordCanIgnoreDeletedReferenceIndexRecords()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntry = GeneralUtility::makeInstance(ReferenceIndexEntry::class, [
            'tablename' => 'sys_file',
            'recuid' => 87,
        ]);
        $actualReferenceIndexEntry = $this->referenceIndexEntryRepository->findOneByReferenceIndexEntry($referenceIndexEntry);
        self::assertNull($actualReferenceIndexEntry, 'ReferenceIndexEntryRepository::findOneByReferenceIndexEntry() finds deleted ReferenceIndexEntry, which MUST NOT be found.');
    }

    /**
     * @test
     */
    public function findOneByReferenceIndexEntryReturnsRightReferenceIndexEntry()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntry = GeneralUtility::makeInstance(ReferenceIndexEntry::class, [
            'tablename' => 'sys_file',
            'recuid' => 47,
        ]);
        $actualReferenceIndexEntry = $this->referenceIndexEntryRepository->findOneByReferenceIndexEntry($referenceIndexEntry);
        self::assertInstanceOf(ReferenceIndexEntry::class, $actualReferenceIndexEntry);
        self::assertEquals('c34e69c870b31cc64badb2b08a688947', $actualReferenceIndexEntry->getRecordHash());
    }

    /**
     * @test
     */
    public function findOneByReferenceIndexEntryCanExcludeTables()
    {
        $this->importDataSetFromFixture('ReferenceIndexEntryRepositoryTest.xml');
        $referenceIndexEntry = GeneralUtility::makeInstance(ReferenceIndexEntry::class, [
            'tablename' => 'sys_file',
            'recuid' => 18,
        ]);
        $actualReferenceIndexEntryWithoutRestrictions = $this->referenceIndexEntryRepository->findOneByReferenceIndexEntry($referenceIndexEntry);
        self::assertInstanceOf(ReferenceIndexEntry::class, $actualReferenceIndexEntryWithoutRestrictions);

        $actualReferenceIndexEntryWithRestrictions = $this->referenceIndexEntryRepository->findOneByReferenceIndexEntry($referenceIndexEntry, ['sys_file_reference']);
        self::assertNull($actualReferenceIndexEntryWithRestrictions);
    }
}
