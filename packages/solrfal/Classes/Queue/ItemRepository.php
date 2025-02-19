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
use ApacheSolrForTypo3\Solr\System\Records\AbstractRepository;
use ApacheSolrForTypo3\Solr\System\Util\SiteUtility;
use ApacheSolrForTypo3\Solrfal\Context\ContextFactory;
use ApacheSolrForTypo3\Solrfal\Event\Repository\AfterFileQueueItemHasBeenRemovedEvent;
use ApacheSolrForTypo3\Solrfal\Event\Repository\AfterMultipleFileQueueItemsHaveBeenRemovedEvent;
use ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeFileQueueItemHasBeenRemovedEvent;
use ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeMultipleFileQueueItemsHaveBeenRemovedEvent;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ItemRepository
 */
class ItemRepository extends AbstractRepository
{
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var Item[]
     */
    protected static array $identityMap = [];

    protected string $table = 'tx_solr_indexqueue_file';

    /**
     * @var string[]
     */
    protected array $fields = [
        'uid',
        'last_update',
        'last_indexed',
        'file',
        'context_type',
        'context_site',
        'context_access_restrictions',
        'context_language',
        'context_record_uid',
        'context_record_table',
        'context_record_field',
        'context_record_pid',
        'context_record_indexing_configuration',
        'context_additional_fields',
        'error',
        'error_message',
        'merge_id',
    ];

    public function __construct(?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher ?? GeneralUtility::makeInstance(EventDispatcher::class);
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *       Getting Objects        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * Finds queue item by uid
     *
     * @throws DBALException
     */
    public function findByUid(int|string $uid): ?Item
    {
        $records = $this->fetchRecordsFromDatabase($this->getSimpleEqWhereClauseExpression('uid', $uid, PDO::PARAM_INT));
        if (!empty($records)) {
            $item = $this->createObject($records[0]);
        } else {
            $item = null;
        }

        return $item;
    }

    /**
     * Finds all queue items
     *
     * @return Item[]
     *
     * @throws DBALException
     */
    public function findAll(): array
    {
        $records = $this->fetchRecordsFromDatabase();
        return $this->createObjectsFromRowArray($records);
    }

    /**
     * Removes items in the index queue filtered by the passed arguments.
     * If no filter is passed, all items get deleted!
     *
     * @param Site[] $sites
     * @param string[] $indexQueueConfigurationNames
     * @param string[] $contextNames
     * @param int[] $itemUids
     * @param int[] $uids
     * @param int[] $languageUids
     * @param int $offset
     * @param int $limit
     *
     * @return Item[]
     *
     * @throws DBALException
     */
    public function findBy(
        array $sites = [],
        array $indexQueueConfigurationNames = [],
        array $contextNames = [],
        array $itemUids = [],
        array $uids = [],
        array $languageUids = [],
        int $offset = 0,
        int $limit = 10,
    ): array {
        $whereClauseExpression = $this->buildByWhereClause(
            $sites,
            $indexQueueConfigurationNames,
            $contextNames,
            $itemUids,
            $uids,
            $languageUids
        );
        return $this->createObjectsFromRowArray($this->fetchRecordsFromDatabase($whereClauseExpression, $offset, $limit));
    }

    /**
     * Finds queue item by file uid
     *
     * @return Item[]
     *
     * @throws DBALException
     */
    public function findByFileUid(int $fileUid): array
    {
        $whereClauseExpression = $this->getSimpleEqWhereClauseExpression('file', $fileUid, PDO::PARAM_INT);
        return $this->createObjectsFromRowArray($this->fetchRecordsFromDatabase($whereClauseExpression));
    }

    /**
     * Finds queue item by file uid
     *
     * @return Item[]
     *
     * @throws DBALException
     */
    public function findByFile(File $file): array
    {
        return $this->findByFileUid($file->getUid());
    }

    /**
     * @return Item[]
     *
     * @throws DBALException
     */
    public function findAllIndexingOutStanding(int $itemCountLimit = null): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $whereClauseExpression = $queryBuilder->where(
            $queryBuilder->expr()->gt('last_update', $queryBuilder->quoteIdentifier('last_indexed'))
        )->getQueryPart('where');
        return $this->createObjectsFromRowArray(
            $this->fetchRecordsFromDatabase($whereClauseExpression, null, $itemCountLimit)
        );
    }

    /**
     * @return string[]
     *
     * @throws DBALException
     */
    public function findAllOutStandingMergeIdSets(int $itemCountLimit = null, int $limitToSiteId = 0): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('merge_id')->from($this->table)
            ->andWhere(
                $queryBuilder->expr()->gt('last_update', $queryBuilder->quoteIdentifier('last_indexed')),
                $queryBuilder->expr()->eq('error', $queryBuilder->quote(0, PDO::PARAM_INT))
            )
            ->groupBy('merge_id');

        if ($itemCountLimit !== null) {
            $queryBuilder->setMaxResults($itemCountLimit);
        }

        if ($limitToSiteId > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('context_site', $queryBuilder->quote($limitToSiteId, PDO::PARAM_INT))
            );
        }

        return $queryBuilder
            ->executeQuery()
            ->fetchFirstColumn();
    }

    /**
     * @return Item[]
     *
     * @throws DBALException
     */
    public function findAllByMergeId(string $mergeId): array
    {
        return $this->createObjectsFromRowArray(
            $this->fetchRecordsFromDatabase($this->getSimpleEqWhereClauseExpression('merge_id', $mergeId))
        );
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *      Counting Objects        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * Counts all items that match the whereClause.
     *
     * @throws DBALException
     */
    protected function countByWhereClause(CompositeExpression $whereClause): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder
            ->count('*')->from($this->table)->andWhere($whereClause)->executeQuery()
            ->fetchOne();
    }

    /**
     * Removes items in the index queue filtered by the passed arguments.
     * If no filter is passed, all items get deleted!
     *
     * @param Site[] $sites
     * @param string[] $indexQueueConfigurationNames
     * @param string[] $contextNames
     * @param int[] $itemUids
     * @param int[] $uids
     * @param int[] $languageUids
     *
     * @throws DBALException
     */
    public function countBy(
        array $sites = [],
        array $indexQueueConfigurationNames = [],
        array $contextNames = [],
        array $itemUids = [],
        array $uids = [],
        array $languageUids = [],
    ): int {
        $whereClause = $this->buildByWhereClause($sites, $indexQueueConfigurationNames, $contextNames, $itemUids, $uids, $languageUids);
        return $this->countByWhereClause($whereClause);
    }

    /**
     * Extracts the number of pending, indexed and erroneous items from the Index Queue.
     *
     * @throws DBALException
     */
    public function getStatisticsBySite(Site $site, string $indexingConfigurationName = ''): QueueStatistic
    {
        return $this->getStatisticsByRootPageId($site->getRootPageId(), $indexingConfigurationName);
    }

    /**
     * Retrieves the statistic for a site by a given rootPageId.
     *
     * @throws DBALException
     */
    public function getStatisticsByRootPageId(int $rootPageId, string $indexingConfigurationName = ''): QueueStatistic
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->add('select', vsprintf('(%s < %s) AS %s', [
                $queryBuilder->quoteIdentifier('last_indexed'),
                $queryBuilder->quoteIdentifier('last_update'),
                $queryBuilder->quoteIdentifier('pending'),
            ]), true)
            ->add('select', vsprintf('(%s) AS %s', [
                $queryBuilder->expr()->eq('error', $queryBuilder->quote(1, PDO::PARAM_INT)),
                $queryBuilder->quoteIdentifier('failed'),
            ]), true)
            ->add('select', $queryBuilder->expr()->count('*', 'count'), true)
            ->from($this->table)
            ->andWhere($queryBuilder->expr()->eq('context_site', $queryBuilder->createNamedParameter($rootPageId, PDO::PARAM_INT)))
            ->groupBy('pending', 'failed');
        if (!empty($indexingConfigurationName)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('context_record_indexing_configuration', $queryBuilder->createNamedParameter($indexingConfigurationName))
            );
        }

        return $this->buildStatisticsObjectFromRows(
            $queryBuilder
                ->executeQuery()
                ->fetchAllAssociative()
        );
    }

    /**
     * Builds a statistics object from the statistic queue database rows.
     *
     * @param list<array{pending:int, failed:int, count:int}>|array<int, array<string, mixed>> $indexQueueStats
     *
     * @todo: Provide proper format for $indexQueueStats PHPDoc
     */
    protected function buildStatisticsObjectFromRows(array $indexQueueStats): QueueStatistic
    {
        /** @var QueueStatistic $statistic */
        $statistic = GeneralUtility::makeInstance(QueueStatistic::class);

        if (empty($indexQueueStats)) {
            return $statistic;
        }

        $failed = $pending = $success = 0;

        foreach ($indexQueueStats as $row) {
            if ($row['failed'] == 1) {
                $failed += (int)$row['count'];
            } elseif ($row['pending'] == 1) {
                $pending += (int)$row['count'];
            } else {
                $success += (int)$row['count'];
            }
        }

        $statistic->setFailedCount($failed);
        $statistic->setPendingCount($pending);
        $statistic->setSuccessCount($success);

        return $statistic;
    }

    /**
     * Counts all Queue\Items which need to updated in Solr
     *
     * @throws DBALException
     */
    public function countIndexingOutstanding(): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder
            ->count('uid')
            ->from($this->table)
            ->andWhere(
                $queryBuilder->expr()->gt('last_update', $queryBuilder->quoteIdentifier('last_indexed')),
                $queryBuilder->expr()->eq('error', $queryBuilder->quote(0, PDO::PARAM_INT))
            )->executeQuery()
            ->fetchOne();
    }

    /**
     * Returns the count of Queue\Items failed to send to solr.
     *
     * @throws DBALException
     */
    public function countFailures(): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder
            ->count('uid')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT))
            )->executeQuery()
            ->fetchOne();
    }

    /**
     * Checks whether an item with same information (context and file) already is in index queue
     *
     * @throws FileDoesNotExistException
     * @throws DBALException
     */
    public function exists(Item $item): bool
    {
        $data = [];
        $data['file'] = $item->getFile()->getUid();
        $data = array_merge($data, $item->getContext()->toArray());
        $data = array_intersect_key($data, array_flip($this->fields));

        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder
                ->count('uid')
                ->from($this->table)
                ->where(
                    $this->getWhereClauseForItemData($data)
                )->executeQuery()
                ->fetchOne() > 0;
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *        Adding Objects        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @throws AspectNotFoundException
     * @throws FileDoesNotExistException
     */
    public function add(Item $item): int
    {
        $data = $this->getEnrichedStandardData($item);

        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->insert($this->table)
            ->values($data)
            ->executeStatement();
        // todo - no identity map update
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *       Updating Objects       *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * Updates the Item
     *
     * @throws AspectNotFoundException
     * @throws FileDoesNotExistException
     */
    public function update(Item $item): int
    {
        $data = $this->getEnrichedStandardData($item);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->update($this->table)->where($this->getWhereClauseForItemData($data));
        foreach ($data as $column => $value) {
            $queryBuilder->set($column, $value);
        }

        return $queryBuilder
            ->executeStatement();
    }

    public function markAsNotIndexed(Item $item): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->update($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->quote($item->getUid(), PDO::PARAM_INT))
            )->set('error', 0)
            ->set('error_message', '')
            ->set('last_indexed', 0)
            ->executeStatement();
    }

    public function markFailed(Item $item, string $errorMessage = ''): int
    {
        $item->setError(true);
        return $this->markFailedByUid($item->getUid(), $errorMessage);
    }

    protected function markFailedByUid(int $itemUid, string $errorMessage = ''): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->update($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->quote($itemUid, PDO::PARAM_INT))
            )
            ->set('error', 1)
            ->set('error_message', $errorMessage)
            ->executeStatement();
    }

    /**
     * @throws AspectNotFoundException
     */
    public function markIndexedSuccessfully(Item $item): int
    {
        return $this->markMultipleIndexedSuccessfully([$item]);
    }

    /**
     * @param Item[] $items
     *
     * @throws AspectNotFoundException
     */
    public function markMultipleIndexedSuccessfully(array $items): int
    {
        $uids = [];
        $executionTime = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp') ?: time();
        foreach ($items as $item) {
            $item->setError(false);
            $item->setLastIndexed($executionTime);
            $uids[] = $item->getUid();
        }
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->update($this->table)
            ->where($queryBuilder->expr()->in('uid', $uids))
            ->set('error', 0)
            ->set('error_message', '')->set('last_indexed', $executionTime)->executeStatement();
    }

    /**
     * @param array<string, string|int|string[]|int[]> $contextFilter array with key = field, value => field value to combine to where clause
     *
     * @return int Affected rows.
     *
     * @throws AspectNotFoundException
     */
    public function markFileUpdated(int $fileUid, array $contextFilter = []): int
    {
        $executionTime = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp') ?: time();

        $contextFilter = array_intersect_key($contextFilter, array_flip($this->fields));

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->update($this->table)
            ->andWhere($queryBuilder->expr()->eq('file', $queryBuilder->createNamedParameter($fileUid, PDO::PARAM_INT)));
        foreach ($contextFilter as $field => $desiredValue) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter($desiredValue))
            );
        }
        return $queryBuilder
            ->set('error', 0)
            ->set('error_message', '')
            ->set('last_update', $executionTime)
            ->executeStatement();
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *      Removing Objects        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * Removes items in the index queue filtered by the passed arguments.
     * If no filter is passed, all items get deleted!
     *
     * @param Site[] $sites
     * @param string[] $indexQueueConfigurationNames
     * @param string[] $contextNames
     * @param int[] $itemUids
     * @param int[] $uids
     * @param int[] $languageUids
     * @param bool $triggerEvents
     *
     * @return int Affected rows.
     *
     * @throws DBALException
     */
    public function removeBy(
        array $sites = [],
        array $indexQueueConfigurationNames = [],
        array $contextNames = [],
        array $itemUids = [],
        array $uids = [],
        array $languageUids = [],
        bool $triggerEvents = true,
    ): int {
        $whereClause = $this->buildByWhereClause($sites, $indexQueueConfigurationNames, $contextNames, $itemUids, $uids, $languageUids);
        return $this->removeByWhereClause($whereClause, $triggerEvents);
    }

    /**
     * Build a CompositeExpression of a whereClause that matches the passed filters.
     *
     * @param Site[] $sites
     * @param string[] $indexQueueConfigurationNames
     * @param string[] $contextNames
     * @param int[] $itemUids
     * @param int[] $uids
     * @param int[] $languageUids
     *
     * @return CompositeExpression
     */
    protected function buildByWhereClause(
        array $sites,
        array $indexQueueConfigurationNames,
        array $contextNames,
        array $itemUids,
        array $uids,
        array $languageUids
    ): CompositeExpression {
        $indexQueueConfigurationList = implode(',', $indexQueueConfigurationNames);
        $contextNameList = implode(',', $contextNames);

        $rootPageIds = array_map('intval', SiteUtility::getRootPageIdsFromSites($sites));
        $itemUids = array_map('intval', $itemUids);
        $uids = array_map('intval', $uids);
        $languageUids = array_map('intval', $languageUids);

        $queryBuilder = $this->getQueryBuilder();
        $whereClause = $queryBuilder->andWhere($queryBuilder->expr()->gt('uid', 0))->getQueryPart('where');
        $whereClause = $this->addInWhereWhenNotEmpty($whereClause, 'context_record_indexing_configuration', $indexQueueConfigurationList);
        $whereClause = $this->addInWhereWhenNotEmpty($whereClause, 'context_type', $contextNameList);

        $whereClause = $this->addInWhereWhenNotEmpty($whereClause, 'context_site', $rootPageIds, -1);
        $whereClause = $this->addInWhereWhenNotEmpty($whereClause, 'context_record_uid', $itemUids, -1);
        $whereClause = $this->addInWhereWhenNotEmpty($whereClause, 'uid', $uids, -1);
        return $this->addInWhereWhenNotEmpty($whereClause, 'context_language', $languageUids, -1);
    }

    /**
     * Adds an in expression from a string list for a certain field when the list is not empty.
     *
     * @param CompositeExpression $whereClause
     * @param string $fieldName
     * @param string|int|string[]|int[] $data
     * @param int $quotingType
     *
     * @return CompositeExpression
     */
    protected function addInWhereWhenNotEmpty(
        CompositeExpression $whereClause,
        string $fieldName,
        string|int|array $data,
        int $quotingType = PDO::PARAM_STR
    ): CompositeExpression {
        if (empty($data)) {
            return $whereClause;
        }
        return $whereClause->with($this->getSimpleInWhereClauseExpression($fieldName, $data, $quotingType));
    }

    /**
     * Removes items in the index queue filtered by the passed table and record uid
     *
     * @throws DBALException
     */
    public function removeByTableAndUid(string $tableName, int $uid): int
    {
        $queryBuilder = $this->getQueryBuilder();
        $whereClause = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq('uid', $queryBuilder->quote($uid, PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('context_record_table', $queryBuilder->quote($tableName))
            )
            ->getQueryPart('where');

        return $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes an Item from the queue and triggers the events to remove it from solr.
     */
    public function remove(Item $item): int
    {
        $this->emitBeforeItemRemovedFromQueue($item);
        $removedCount = $this->removeItemFromDatabase($item);
        $this->emitItemRemovedFromQueue($item);
        if (array_key_exists($item->getUid(), self::$identityMap)) {
            unset(self::$identityMap[$item->getUid()]);
        }
        return $removedCount;
    }

    /**
     * Removes the items from the database only.
     */
    protected function removeItemFromDatabase(Item $item): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($item->getUid(), PDO::PARAM_INT))
            )->executeStatement();
    }

    /**
     * @throws DBALException
     */
    public function removeByFile(File $file): int
    {
        return $this->removeByFileUid($file->getUid());
    }

    /**
     * Removes an file index queue entry
     *
     * @throws DBALException
     */
    public function removeByFileUid(int $fileUid): int
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('file', $fileUid, PDO::PARAM_INT);
        return $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes all entries of a certain site from the File Index Queue.
     *
     * @throws DBALException
     */
    public function removeBySite(Site $site): int
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT);
        return $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes all entries of a certain site from the File Index Queue.
     *
     * @throws DBALException
     */
    public function removeBySiteAndContext(Site $site, string $contextType): int
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT)
            ->with($this->getSimpleEqWhereClauseExpression('context_type', $contextType));
        return $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes all entries of a certain site from the File Index Queue.
     *
     * @throws DBALException
     */
    public function purgeContext(string $contextType): int
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_type', $contextType);
        return $this->removeByWhereClause($whereClause);
    }

    /**
     * @throws DBALException
     */
    public function removeByTableInRecordContext(Site $site, string $tableName): int
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression(
            'context_site',
            $site->getRootPageId(),
            PDO::PARAM_INT
        )->with($this->getSimpleEqWhereClauseExpression(
            'context_type',
            'record'
        ))->with($this->getSimpleEqWhereClauseExpression('context_record_table', $tableName));
        return $this->removeByWhereClause($whereClause);
    }

    /**
     * @throws DBALException
     */
    public function removeByIndexingConfigurationInRecordContext(
        Site $site,
        string $indexingConfiguration,
    ): int {
        $whereClause = $this->getSimpleEqWhereClauseExpression(
            'context_site',
            $site->getRootPageId(),
            PDO::PARAM_INT
        )->with($this->getSimpleEqWhereClauseExpression('context_type', 'record'))
            ->with($this->getSimpleEqWhereClauseExpression(
                'context_record_indexing_configuration',
                $indexingConfiguration
            ));

        return $this->removeByWhereClause($whereClause);
    }

    /**
     * @throws DBALException
     */
    public function removeByTableAndUidInContext(
        string $context,
        Site $site,
        string $tableName,
        int $contextRecordUid,
        ?int $fileUidToRemoveExplicitly = null,
        ?int $languageUid = null
    ): int {
        $whereClause = $this->getSimpleEqWhereClauseExpression(
            'context_site',
            $site->getRootPageId(),
            PDO::PARAM_INT
        )->with($this->getSimpleEqWhereClauseExpression(
            'context_type',
            $context
        ))->with($this->getSimpleEqWhereClauseExpression(
            'context_record_table',
            $tableName
        ))->with($this->getSimpleEqWhereClauseExpression(
            'context_record_uid',
            $contextRecordUid,
            PDO::PARAM_INT
        ));

        if ($languageUid !== null && $languageUid > 0) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression(
                'context_language',
                $languageUid
            ));
        }

        if ($fileUidToRemoveExplicitly !== null) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression(
                'file',
                $fileUidToRemoveExplicitly,
                PDO::PARAM_INT
            ));
        }

        return $this->removeByWhereClause($whereClause);
    }

    /**
     * @throws DBALException
     */
    public function removeOldEntriesFromFieldInRecordContext(
        Site $site,
        string $tableName,
        int $contextRecordUid,
        int $language,
        string $fieldName,
        int ...$relatedFiles,
    ): int {
        array_walk($relatedFiles, 'intval');
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT)
            ->with($this->getSimpleEqWhereClauseExpression('context_type', 'record'))
            ->with($this->getSimpleEqWhereClauseExpression('context_record_table', $tableName))
            ->with($this->getSimpleEqWhereClauseExpression('context_record_uid', $contextRecordUid, PDO::PARAM_INT))
            ->with($this->getSimpleEqWhereClauseExpression('context_language', $language, PDO::PARAM_INT))
            ->with($this->getSimpleEqWhereClauseExpression('context_record_field', $fieldName));
        if (!empty($relatedFiles)) {
            $queryBuilder = $this->getQueryBuilder();
            $whereClause = $whereClause->with($queryBuilder->andWhere($queryBuilder->expr()->notIn('file', $relatedFiles))->getQueryPart('where'));
        }

        return $this->removeByWhereClause($whereClause);
    }

    /**
     * @throws DBALException
     */
    public function removeOldEntriesInPageContext(
        Site $site,
        int $pageId,
        int $language = null,
        int ...$relatedFiles,
    ): int {
        array_walk($relatedFiles, 'intval');
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT)
            ->with($this->getSimpleEqWhereClauseExpression('context_type', 'page'))
            ->with($this->getSimpleEqWhereClauseExpression('context_record_pid', $pageId, PDO::PARAM_INT));

        if ($language !== null) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression('context_language', $language, PDO::PARAM_INT));
        }

        if (!empty($relatedFiles)) {
            $queryBuilder = $this->getQueryBuilder();
            $whereClause = $whereClause->with($queryBuilder->andWhere($queryBuilder->expr()->notIn('file', $relatedFiles))->getQueryPart('where'));
        }

        return $this->removeByWhereClause($whereClause);
    }

    /**
     * Remove queue entries by given file storage uid, considering the site
     *
     * @throws DBALException
     */
    public function removeByFileStorage(
        Site $site,
        int $fileStorageUid,
        string $indexingConfiguration = null,
    ): int {
        $queryBuilder = $this->getQueryBuilder();
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT)
            ->with($this->getSimpleEqWhereClauseExpression('context_type', 'storage'))
            ->with(
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->like(
                        'context_additional_fields',
                        $queryBuilder->quote(
                            '%"fileStorage":' . $queryBuilder->escapeLikeWildcards((string)$fileStorageUid) . '%'
                        )
                    )
                )->getQueryPart('where')
            );

        if ($indexingConfiguration !== null) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression(
                'context_record_indexing_configuration',
                $indexingConfiguration
            ));
        }

        return $this->removeByWhereClause($whereClause);
    }

    /**
     * Finds indexing errors for the current site
     *
     * @return list<array<string,mixed>>|array{
     *     uid: int,
     *     last_update: int,
     *     last_indexed: int,
     *     file: int,
     *     merge_id: string,
     *     context_type: string,
     *     context_site: int,
     *     context_access_restrictions: string,
     *     context_language: int,
     *     context_record_indexing_configuration: string,
     *     context_record_uid: int,
     *     context_record_table: string,
     *     context_record_field: string,
     *     context_record_page: int,
     *     context_additional_fields: string,
     *     error_message: string,
     *     error: int,
     *     context_record_pid: int,
     * }
     *
     * @throws DBALException
     */
    public function findErrorsBySite(Site $contextSite): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)->andWhere($queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT)), $queryBuilder->expr()->eq('context_site', $contextSite->getRootPageId()))->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Resets all the errors for all index queue items.
     */
    public function flushErrorsBySite(Site $contextSite): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->update($this->table)
            ->set('error', 0)
            ->set('error_message', '')->andWhere($queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT)), $queryBuilder->expr()->eq('context_site', $queryBuilder->createNamedParameter($contextSite->getRootPageId(), PDO::PARAM_INT)))->executeStatement();
    }

    /**
     * Resets all the errors for all index queue items.
     */
    public function flushAllErrors(): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->update($this->table)
            ->set('error', 0)
            ->set('error_message', '')
            ->andWhere(
                $queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT))
            )->executeStatement();
    }

    /**
     * Removes items by whereClause.
     *
     * @throws DBALException
     */
    protected function removeByWhereClause(
        CompositeExpression $whereClause,
        bool $emitSignals = true,
    ): int {
        $queryBuilder = $this->getQueryBuilder();
        $uidsToDelete = $queryBuilder->select('uid')
            ->from($this->table)->andWhere($whereClause)->executeQuery()
            ->fetchFirstColumn();

        if ($uidsToDelete === []) {
            return 0;
        }

        if ($emitSignals) {
            $this->emitBeforeMultipleItemsRemovedFromQueue($uidsToDelete);
        }

        $queryBuilder = $this->getQueryBuilder();
        $deletedCount = $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->in('uid', $uidsToDelete)
            )->executeStatement();

        if ($emitSignals) {
            $this->emitMultipleItemsRemovedFromQueue($uidsToDelete);
        }

        foreach ($uidsToDelete as $deletedUid) {
            if (array_key_exists($deletedUid, self::$identityMap)) {
                unset(self::$identityMap[$deletedUid]);
            }
        }

        return $deletedCount;
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *       Objects Factory        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @param list<array{
     *    uid: int,
     *    last_update: int,
     *    last_indexed: int,
     *    file: int,
     *    merge_id: string,
     *    context_type: string,
     *    context_site: int,
     *    context_access_restrictions: string,
     *    context_language: int,
     *    context_record_indexing_configuration: string,
     *    context_record_uid: int,
     *    context_record_pid: int,
     *    context_record_table: string,
     *    context_record_field: string,
     *    context_additional_fields: string,
     *    error_message: string,
     *    error: int,
     *  }>|array<int, array<string, mixed>> $rows
     *
     * @return Item[]
     */
    protected function createObjectsFromRowArray(array $rows): array
    {
        $itemArray = [];
        foreach ($rows as $singleRow) {
            try {
                $object = $this->createObject($singleRow);
                $itemArray[] = $object;
            } catch (Throwable $e) {
                $this->markFailedByUid(
                    $singleRow['uid'],
                    $e->getMessage() . '[' . $e->getCode() . ']'
                );
            }
        }

        return $itemArray;
    }

    /**
     * @param array{
     *   uid: int,
     *   last_update: int,
     *   last_indexed: int,
     *   file: int,
     *   merge_id: string,
     *   context_type: string,
     *   context_site: int,
     *   context_access_restrictions: string,
     *   context_language: int,
     *   context_record_indexing_configuration: string,
     *   context_record_uid: int,
     *   context_record_pid: int,
     *   context_record_table: string,
     *   context_record_field: string,
     *   context_additional_fields: string,
     *   error_message: string,
     *   error: int,
     * }|array<string, mixed> $row
     * @throws DBALException
     */
    protected function createObject(array $row): Item
    {
        $uid = (int)($row['uid']);
        if (!array_key_exists($uid, self::$identityMap)) {
            $context = $this->getContextFactory()->getByRecord($row);
            $item = GeneralUtility::makeInstance(
                Item::class,
                $row['file'],
                $context,
                $row['uid'],
                $row['error'] == 1,
                $row['last_update'],
                $row['last_indexed'],
                $row['merge_id'],
                $row['error_message']
            );
            self::$identityMap[$uid] = $item;
        } else {
            $item = self::$identityMap[$uid];
            $item->setError($row['error'] == 1);
            $item->setLastIndexed($row['last_indexed']);
            $item->setLastUpdated($row['last_update']);
        }

        return self::$identityMap[$uid];
    }

    protected function getContextFactory(): ContextFactory
    {
        return GeneralUtility::makeInstance(ContextFactory::class);
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *           Signals            *
     *                              *
     *++++++++++++++++++++++++++++++*/

    protected function emitItemRemovedFromQueue(Item $item): void
    {
        $this->eventDispatcher->dispatch(new AfterFileQueueItemHasBeenRemovedEvent($item));
    }

    /**
     * @param int[] $itemUids
     */
    protected function emitMultipleItemsRemovedFromQueue(array $itemUids): void
    {
        $this->eventDispatcher->dispatch(new AfterMultipleFileQueueItemsHaveBeenRemovedEvent($itemUids));
    }

    protected function emitBeforeItemRemovedFromQueue(Item $item): void
    {
        $this->eventDispatcher->dispatch(new BeforeFileQueueItemHasBeenRemovedEvent($item));
    }

    /**
     * @param int[] $itemUids
     */
    protected function emitBeforeMultipleItemsRemovedFromQueue(array $itemUids): void
    {
        $this->eventDispatcher->dispatch(new BeforeMultipleFileQueueItemsHaveBeenRemovedEvent($itemUids));
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *           Helpers            *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @param string $fieldName
     * @param string|int|string[]|int[] $value
     * @param int $quotingType
     * @return CompositeExpression
     */
    protected function getSimpleEqWhereClauseExpression(
        string $fieldName,
        string|int|array $value,
        int $quotingType = PDO::PARAM_STR
    ): CompositeExpression {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->andWhere(
            $queryBuilder->expr()->eq($fieldName, $queryBuilder->quote($value, $quotingType))
        )->getQueryPart('where');
    }

    /**
     * @param string $fieldName
     * @param string|int|string[]|int[] $value
     * @param int $quotingType
     *
     * @return CompositeExpression
     */
    protected function getSimpleInWhereClauseExpression(
        string $fieldName,
        string|int|array $value,
        int $quotingType = PDO::PARAM_STR
    ): CompositeExpression {
        $queryBuilder = $this->getQueryBuilder();
        $quotedValue = $quotingType >= 0 ? $queryBuilder->quote($value, $quotingType) : $value;
        return $queryBuilder->andWhere(
            $queryBuilder->expr()->in($fieldName, $quotedValue)
        )->getQueryPart('where');
    }

    /**
     * @param array<string, string|int|bool|null> $data
     */
    protected function getWhereClauseForItemData(array $data): CompositeExpression
    {
        $queryBuilder = $this->getQueryBuilder();

        if ((int)($data['uid'] ?? 0) > 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('uid', $queryBuilder->quote((int)$data['uid'], PDO::PARAM_INT)));
        } else {
            // remove entries not relevant for existing check
            unset($data['context_access_restrictions']);
            unset($data['last_update']);
            unset($data['last_indexed']);
            unset($data['error']);
            unset($data['error_message']);
            foreach ($data as $field => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($field, $queryBuilder->quote($value)));
            }
        }
        return $queryBuilder->getQueryPart('where');
    }

    /**
     * @return list<array{
     *   uid: int|string,
     *   last_update: int,
     *   last_indexed: int,
     *   file: int,
     *   merge_id: string,
     *   context_type: string,
     *   context_site: int,
     *   context_access_restrictions: string,
     *   context_language: int,
     *   context_record_indexing_configuration: string,
     *   context_record_uid: int,
     *   context_record_table: string,
     *   context_record_field: string,
     *   context_record_page: int,
     *   context_additional_fields: string,
     *   error_message: string,
     *   error: int,
     *   context_record_pid: int,
     *  }>|list<array<string,mixed>>
     *
     * @throws DBALException
     */
    protected function fetchRecordsFromDatabase(
        CompositeExpression $whereClauseExpression = null,
        int $offset = null,
        int $limit = null
    ): array {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select(...$this->fields)->from($this->table);

        if ($whereClauseExpression !== null) {
            $queryBuilder->andWhere($whereClauseExpression);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }
        return $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @return array<string, string|int|bool|null>
     *
     * @throws AspectNotFoundException
     * @throws FileDoesNotExistException
     */
    protected function getEnrichedStandardData(Item $item): array
    {
        $data = [];
        $data['file'] = $item->getFile()->getUid();
        $data['last_update'] = GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('date', 'timestamp') ?: time();
        $data['merge_id'] = $item->getMergeId();

        $data = array_merge($data, $item->getContext()->toArray());
        return array_intersect_key($data, array_flip($this->fields));
    }
}
