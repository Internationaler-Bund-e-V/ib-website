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

namespace ApacheSolrForTypo3\Solrfal\Domain\Repository;

use ApacheSolrForTypo3\Solr\System\Records\AbstractRepository;
use ApacheSolrForTypo3\Solrfal\Domain\Model\ReferenceIndexEntry;
use Doctrine\DBAL\Exception as DBALException;
use PDO;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Reference index entry repository
 */
class ReferenceIndexEntryRepository extends AbstractRepository
{
    protected string $table = 'sys_refindex';

    /**
     * Database connection
     */
    protected Connection $databaseConnection;

    /**
     * Class constructor
     */
    public function __construct(?Connection $databaseConnection = null)
    {
        $this->databaseConnection = $databaseConnection ?? GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->table);
    }

    /**
     * Returns reference index entry
     *
     * @param array<string, string|int|bool|null>|list<array<string,mixed>> $referenceData The sys_file_reference record to use for instantiation of ReferenceIndexEntry
     */
    protected function getReferenceIndexEntry(array $referenceData): ReferenceIndexEntry
    {
        return GeneralUtility::makeInstance(ReferenceIndexEntry::class, $referenceData);
    }

    /**
     * Find by reference record
     *
     * @param string $referenceTable The table name of reference
     * @param int $referenceUid The UID of sys_file_reference
     * @param string[] $excludeTables The list of table names to exclude
     * @param string[] $limitToTables The list of table names to search for
     *
     * @return ReferenceIndexEntry[]
     *
     * @throws DBALException
     */
    public function findByReferenceRecord(
        string $referenceTable,
        int $referenceUid,
        array $excludeTables = [],
        array $limitToTables = []
    ): array {
        $queryBuilder = $this->gedQueryBuilderWithExcludedTablesRestrictions(...$excludeTables);
        if (!empty($limitToTables)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('tablename', array_map([$this, 'quoteString'], $limitToTables)));
        }

        $this->restrictByRefTableAndRefUid($referenceTable, $referenceUid, $queryBuilder);
        $referencesData = $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();

        $referenceIndexEntries = [];
        foreach ($referencesData as $referenceData) {
            $referenceIndexEntries[] = $this->getReferenceIndexEntry($referenceData);
        }

        return $referenceIndexEntries;
    }

    /**
     * Find reference by reference index entry
     *
     * @param ReferenceIndexEntry $referenceIndexEntry The ReferenceIndexEntry object to use
     * @param string[] $excludeTables The list of tables to exclude from search
     *
     * @return ReferenceIndexEntry|null
     * @throws DBALException
     */
    public function findOneByReferenceIndexEntry(
        ReferenceIndexEntry $referenceIndexEntry,
        array $excludeTables = []
    ): ?ReferenceIndexEntry {
        $queryBuilder = $this->gedQueryBuilderWithExcludedTablesRestrictions(...$excludeTables);
        $this->restrictByRefTableAndRefUid($referenceIndexEntry->getTableName(), $referenceIndexEntry->getRecordUid(), $queryBuilder);
        $referenceData = $queryBuilder
            ->executeQuery()
            ->fetchAssociative();

        if (!empty($referenceData)) {
            return $this->getReferenceIndexEntry($referenceData);
        }

        return null;
    }

    /**
     * Instantiates QueryBuilder with excluded table names restrictions
     */
    protected function gedQueryBuilderWithExcludedTablesRestrictions(string ...$excludeTables): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder()->select('*')->from($this->table);
        $queryBuilder->getRestrictions()->removeAll();
        if (!empty($excludeTables)) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('tablename', array_map([$this, 'quoteString'], $excludeTables)));
        }
        return $queryBuilder;
    }

    /**
     * Adds restriction for given ref_table and ref_uid
     */
    protected function restrictByRefTableAndRefUid(
        string $ref_table,
        int $ref_uid,
        QueryBuilder $queryBuilder,
    ): void {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq('ref_table', $queryBuilder->createNamedParameter($ref_table)),
            $queryBuilder->expr()->eq('ref_uid', $queryBuilder->createNamedParameter($ref_uid, PDO::PARAM_INT))
        );
    }

    /**
     * Quotes $stringValue as \PDO::PARAM_STR
     * Usable fir IN() expressions
     */
    protected function quoteString(string $stringValue): string
    {
        return $this->databaseConnection->quote($stringValue, PDO::PARAM_STR);
    }
}
