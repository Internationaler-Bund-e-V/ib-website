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
use ApacheSolrForTypo3\Solrfal\Context\ContextFactory;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use PDO;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class PersistenceManager
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class ItemRepository extends AbstractRepository
{
    /**
     * @var Item[]
     */
    protected static array $identityMap = [];

    /**
     * @var string
     */
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
        'context_record_page',
        'context_record_indexing_configuration',
        'context_additional_fields',
        'error',
        'error_message',
        'merge_id',
    ];

    /*++++++++++++++++++++++++++++++*
     *                              *
     *       Getting Objects        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * Finds queue item by uid
     *
     * @param int $uid
     * @return Item|null
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function findByUid(int $uid): ?Item
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
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
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
     * @param array $sites
     * @param array $indexQueueConfigurationNames
     * @param array $contextName
     * @param array $itemUids
     * @param array $uids
     * @param array $languageUids
     * @param int $offset
     * @param int $limit
     * @return Item[]|array
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function findBy(
        array $sites = [],
        array $indexQueueConfigurationNames = [],
        array $contextName = [],
        array $itemUids = [],
        array $uids = [],
        array $languageUids = [],
        int $offset = 0,
        int $limit = 10
    ): array {
        $whereClauseExpression = $this->buildByWhereClause(
            $sites,
            $indexQueueConfigurationNames,
            $contextName,
            $itemUids,
            $uids,
            $languageUids
        );
        return $this->createObjectsFromRowArray($this->fetchRecordsFromDatabase($whereClauseExpression, $offset, $limit));
    }

    /**
     * Finds queue item by file uid
     *
     * @param int $fileUid
     * @return Item[]
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function findByFileUid(int $fileUid): array
    {
        $whereClauseExpression = $this->getSimpleEqWhereClauseExpression('file', $fileUid, PDO::PARAM_INT);
        return $this->createObjectsFromRowArray($this->fetchRecordsFromDatabase($whereClauseExpression));
    }

    /**
     * Finds queue item by file uid
     *
     * @param File $file
     * @return Item[]
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function findByFile(File $file): array
    {
        return $this->findByFileUid($file->getUid());
    }

    /**
     * @param null $itemCountLimit
     *
     * @return Item[]
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function findAllIndexingOutStanding($itemCountLimit = null): array
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
     * @param int|null $itemCountLimit
     * @param int $limitToSiteId
     *
     * @return Item[]
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
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

        if (null !== $itemCountLimit) {
            $queryBuilder->setMaxResults($itemCountLimit);
        }

        if ($limitToSiteId > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('context_site', $queryBuilder->quote($limitToSiteId, PDO::PARAM_INT))
            );
        }

        return $queryBuilder
            ->execute()
            ->fetchFirstColumn();
    }

    /**
     * @param string $mergeId
     *
     * @return Item[]
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
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
     * @param CompositeExpression $whereClause
     * @return int
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    protected function countByWhereClause(CompositeExpression $whereClause): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder
            ->count('*')->from($this->table)
            ->andWhere($whereClause)
            ->execute()
            ->fetchOne();
    }

    /**
     * Removes items in the index queue filtered by the passed arguments.
     * If no filter is passed, all items get deleted!
     *
     * @param array $sites
     * @param array $indexQueueConfigurationNames
     * @param array $contextName
     * @param array $itemUids
     * @param array $uids
     * @param array $languageUids
     * @return int
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function countBy(
        array $sites = [],
        array $indexQueueConfigurationNames = [],
        array $contextName = [],
        array $itemUids = [],
        array $uids = [],
        array $languageUids = []
    ): int {
        $whereClause = $this->buildByWhereClause($sites, $indexQueueConfigurationNames, $contextName, $itemUids, $uids, $languageUids);
        return $this->countByWhereClause($whereClause);
    }

    /**
     * Extracts the number of pending, indexed and erroneous items from the
     * Index Queue.
     *
     * @param Site $site
     * @param string $indexingConfigurationName
     *
     * @return QueueStatistic
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function getStatisticsBySite(Site $site, string $indexingConfigurationName = ''): QueueStatistic
    {
        return $this->getStatisticsByRootPageId($site->getRootPageId(), $indexingConfigurationName);
    }

    /**
     * Retrieves the statistic for a site by a given rootPageId.
     *
     * @param int $rootPageId
     * @param string $indexingConfigurationName
     *
     * @return QueueStatistic
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
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

        return $this->buildStatisticsObjectFromRows($queryBuilder->execute()->fetchAllAssociative());
    }

    /**
     * Builds a statistics object from the statistic queue database rows.
     *
     * @param array $indexQueueStats
     * @return QueueStatistic
     */
    protected function buildStatisticsObjectFromRows(array $indexQueueStats): QueueStatistic
    {
        /** @var $statistic QueueStatistic */
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
     * @return int
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function countIndexingOutstanding(): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder->count('uid')->from($this->table)
           ->andWhere(
               $queryBuilder->expr()->gt('last_update', $queryBuilder->quoteIdentifier('last_indexed')),
               $queryBuilder->expr()->eq('error', $queryBuilder->quote(0, PDO::PARAM_INT))
           )
            ->execute()
            ->fetchOne();
    }

    /**
     * Returns the count of Queue\Items failed to send to solr.
     *
     * @return int
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function countFailures(): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder->count('uid')->from($this->table)
            ->where($queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT)))
            ->execute()->fetchOne();
    }

    /**
     * Checks whether an item with same information (context and file) already is in index queue
     *
     * @param Item $item
     *
     * @return bool
     * @throws DBALDriverException
     * @throws FileDoesNotExistException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function exists(Item $item): bool
    {
        $data = [];
        $data['file'] = $item->getFile()->getUid();
        $data = array_merge($data, $item->getContext()->toArray());
        $data = array_intersect_key($data, array_flip($this->fields));

        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder->count('uid')->from($this->table)
            ->where($this->getWhereClauseForItemData($data))
            ->execute()->fetchOne() > 0;
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *        Adding Objects        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @param Item $item
     *
     * @throws AspectNotFoundException
     * @throws FileDoesNotExistException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function add(Item $item)
    {
        $data = $this->getEnrichedStandardData($item);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insert($this->table)->values($data)->execute();
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
     * @param Item $item
     *
     * @return int the number of affected rows
     * @throws AspectNotFoundException
     * @throws FileDoesNotExistException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function update(Item $item): int
    {
        $data = $this->getEnrichedStandardData($item);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->update($this->table)->where($this->getWhereClauseForItemData($data));
        foreach ($data as $column => $value) {
            $queryBuilder->set($column, $value);
        }

        return $queryBuilder->execute();
    }

    /**
     * @param Item $item
     *
     * @return int the number of affected rows
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function markAsNotIndexed(Item $item): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder->update($this->table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->quote($item->getUid(), PDO::PARAM_INT)))
            ->set('error', 0)
            ->set('error_message', '')
            ->set('last_indexed', 0)
            ->execute();
    }

    /**
     * @param Item $item
     * @param string $errorMessage
     *
     * @return int the number of affected rows
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function markFailed(Item $item, string $errorMessage = ''): int
    {
        $item->setError(true);
        $queryBuilder = $this->getQueryBuilder();
        return (int)$queryBuilder->update($this->table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->quote($item->getUid(), PDO::PARAM_INT)))
            ->set('error', 1)
            ->set('error_message', $errorMessage)
            ->execute();
    }

    /**
     * @param Item $item
     *
     * @throws AspectNotFoundException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function markIndexedSuccessfully(Item $item)
    {
        $this->markMultipleIndexedSuccessfully([$item]);
    }

    /**
     * @param Item[] $items
     *
     * @throws DBALException|\Doctrine\DBAL\DBALException
     * @throws AspectNotFoundException
     */
    public function markMultipleIndexedSuccessfully(array $items)
    {
        $uids = [];
        $executionTime = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp') ?: time();
        foreach ($items as $item) {
            $item->setError(false);
            $item->setLastIndexed($executionTime);
            $uids[] = $item->getUid();
        }
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->update($this->table)
            ->where($queryBuilder->expr()->in('uid', $uids))
            ->set('error', 0)
            ->set('error_message', '')
            ->set('last_indexed', $executionTime)
            ->execute();
    }

    /**
     * @param int $fileUid
     * @param array $contextFilter array with key = field, value => field value to combine to where clause
     *
     * @throws AspectNotFoundException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function markFileUpdated(int $fileUid, array $contextFilter = [])
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
        $queryBuilder
            ->set('error', 0)
            ->set('error_message', '')
            ->set('last_update', $executionTime)
            ->execute();
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
     * @param array $sites
     * @param array $indexQueueConfigurationNames
     * @param array $contextName
     * @param array $itemUids
     * @param array $uids
     * @param array $languageUids
     * @param bool $triggerSignals Should signals (e.g. for deletion in solr be triggered)
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeBy(
        array $sites = [],
        array $indexQueueConfigurationNames = [],
        array $contextName = [],
        array $itemUids = [],
        array $uids = [],
        array $languageUids = [],
        bool $triggerSignals = true
    ) {
        $whereClause = $this->buildByWhereClause($sites, $indexQueueConfigurationNames, $contextName, $itemUids, $uids, $languageUids);
        $this->removeByWhereClause($whereClause, $triggerSignals);
    }

    /**
     * Build a CompositeExpression of a whereClause that matches the passed filters.
     *
     * @param array $sites
     * @param array $indexQueueConfigurationNames
     * @param array $contextName
     * @param array $itemUids
     * @param array $uids
     * @param array $languageUids
     * @return CompositeExpression
     */
    protected function buildByWhereClause(
        array $sites,
        array $indexQueueConfigurationNames,
        array $contextName,
        array $itemUids,
        array $uids,
        array $languageUids
    ): CompositeExpression {
        $indexQueueConfigurationList = implode(',', $indexQueueConfigurationNames);
        $contextNameList = implode(',', $contextName);

        $rootPageIds = array_map('intval', Site::getRootPageIdsFromSites($sites));
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
     * @param string|array $data
     * @param int $quotingType
     * @return CompositeExpression
     */
    protected function addInWhereWhenNotEmpty(
        CompositeExpression $whereClause,
        string $fieldName,
        $data,
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
     * @param string $tableName
     * @param int $uid
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByTableAndUid(string $tableName, int $uid): void
    {
        $queryBuilder = $this->getQueryBuilder();
        $whereClause = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq('uid', $queryBuilder->quote($uid, PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('context_record_table', $queryBuilder->quote($tableName))
            )
            ->getQueryPart('where');

        $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes an Item from the queue and triggers the events to remove it from solr.
     *
     * @param Item $item
     *
     * @throws DBALException|\Doctrine\DBAL\DBALException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function remove(Item $item)
    {
        $this->emitBeforeItemRemovedFromQueue($item);
        $this->removeItemFromDatabase($item);
        $this->emitItemRemovedFromQueue($item);
        if (array_key_exists($item->getUid(), self::$identityMap)) {
            unset(self::$identityMap[$item->getUid()]);
        }
    }

    /**
     * Removes the items from the database only.
     *
     * @param Item $item
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    protected function removeItemFromDatabase(Item $item)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->delete($this->table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($item->getUid(), PDO::PARAM_INT)))
            ->execute();
    }

    /**
     * @param File $file
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByFile(File $file)
    {
        $this->removeByFileUid($file->getUid());
    }

    /**
     * Removes an file index queue entry
     *
     * @param int $fileUid
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByFileUid(int $fileUid)
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('file', $fileUid, PDO::PARAM_INT);
        $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes all entries of a certain site from the File Index Queue.
     *
     * @param Site $site The site to remove items for.
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeBySite(Site $site)
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT);
        $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes all entries of a certain site from the File Index Queue.
     *
     * @param Site $site The site to remove items for.
     * @param string $contextType
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeBySiteAndContext(Site $site, string $contextType)
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT)
            ->with($this->getSimpleEqWhereClauseExpression('context_type', $contextType));
        $this->removeByWhereClause($whereClause);
    }

    /**
     * Removes all entries of a certain site from the File Index Queue.
     *
     * @param string $contextType
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function purgeContext(string $contextType)
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_type', $contextType);
        $this->removeByWhereClause($whereClause);
    }

    /**
     * @param Site $site
     * @param string $tableName
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByTableInRecordContext(Site $site, string $tableName)
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression(
            'context_site',
            $site->getRootPageId(),
            PDO::PARAM_INT
        )->with($this->getSimpleEqWhereClauseExpression(
            'context_type',
            'record'
        ))->with($this->getSimpleEqWhereClauseExpression('context_record_table', $tableName));
        $this->removeByWhereClause($whereClause);
    }

    /**
     * @param Site $site
     * @param string $indexingConfiguration
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByIndexingConfigurationInRecordContext(Site $site, string $indexingConfiguration)
    {
        $whereClause = $this->getSimpleEqWhereClauseExpression(
            'context_site',
            $site->getRootPageId(),
            PDO::PARAM_INT
        )->with($this->getSimpleEqWhereClauseExpression('context_type', 'record'))
            ->with($this->getSimpleEqWhereClauseExpression(
                'context_record_indexing_configuration',
                $indexingConfiguration
            ));

        $this->removeByWhereClause($whereClause);
    }

    /**
     * @param string $context
     * @param Site $site
     * @param string $tableName
     * @param int $contextRecordUid Context record Uid
     * @param int|null $fileUid optional If set, only given file will be removed
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByTableAndUidInContext(string $context, Site $site, string $tableName, int $contextRecordUid, int $fileUid = null)
    {
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

        if (null !== $fileUid) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression(
                'file',
                $fileUid,
                PDO::PARAM_INT
            ));
        }

        $this->removeByWhereClause($whereClause);
    }

    /**
     * @param Site $site
     * @param string $tableName
     * @param int $contextRecordUid
     * @param int $language
     * @param string $fieldName
     * @param int ...$relatedFiles
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeOldEntriesFromFieldInRecordContext(Site $site, string $tableName, int $contextRecordUid, int $language, string $fieldName, int ...$relatedFiles)
    {
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

        $this->removeByWhereClause($whereClause);
    }

    /**
     * @param Site $site
     * @param int $pageId
     * @param int|null $language
     * @param int ...$relatedFiles
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeOldEntriesInPageContext(Site $site, int $pageId, int $language = null, int ...$relatedFiles)
    {
        array_walk($relatedFiles, 'intval');
        $whereClause = $this->getSimpleEqWhereClauseExpression('context_site', $site->getRootPageId(), PDO::PARAM_INT)
            ->with($this->getSimpleEqWhereClauseExpression('context_type', 'page'))
            ->with($this->getSimpleEqWhereClauseExpression('context_record_page', $pageId, PDO::PARAM_INT));

        if (null !== $language) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression('context_language', $language, PDO::PARAM_INT));
        }

        if (!empty($relatedFiles)) {
            $queryBuilder = $this->getQueryBuilder();
            $whereClause = $whereClause->with($queryBuilder->andWhere($queryBuilder->expr()->notIn('file', $relatedFiles))->getQueryPart('where'));
        }

        $this->removeByWhereClause($whereClause);
    }

    /**
     * Remove queue entries by given file storage uid, considering the site
     *
     * @param Site $site
     * @param int $fileStorageUid
     * @param string|null $indexingConfiguration
     *
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function removeByFileStorage(Site $site, int $fileStorageUid, string $indexingConfiguration = null)
    {
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

        if (null !== $indexingConfiguration) {
            $whereClause = $whereClause->with($this->getSimpleEqWhereClauseExpression(
                'context_record_indexing_configuration',
                $indexingConfiguration
            ));
        }

        $this->removeByWhereClause($whereClause);
    }

    /**
     * Finds indexing errors for the current site
     *
     * @param Site $contextSite
     * @return array Error items for the current site's Index Queue
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function findErrorsBySite(Site $contextSite): array
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->andWhere(
                $queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('context_site', $contextSite->getRootPageId())
            )
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * Resets all the errors for all index queue items.
     *
     * @param Site $contextSite
     * @return int affected rows
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    public function flushErrorsBySite(Site $contextSite): int
    {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder
            ->update($this->table)
            ->set('error', 0)
            ->set('error_message', '')
            ->andWhere(
                $queryBuilder->expr()->eq('error', $queryBuilder->createNamedParameter(1, PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('context_site', $queryBuilder->createNamedParameter($contextSite->getRootPageId(), PDO::PARAM_INT))
            )
            ->execute();
    }

    /**
     * Resets all the errors for all index queue items.
     *
     * @return int affected rows
     * @throws DBALException|\Doctrine\DBAL\DBALException
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
            )
            ->execute();
    }

    /**
     * Removes items by whereClause.
     *
     * @param CompositeExpression $whereClause
     * @param bool $emitSignals
     * @throws DBALDriverException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    protected function removeByWhereClause(CompositeExpression $whereClause, bool $emitSignals = true)
    {
        $queryBuilder = $this->getQueryBuilder();
        $uidsToDelete = $queryBuilder->select('uid')
            ->from($this->table)
            ->andWhere($whereClause)
            ->execute()
            ->fetchFirstColumn();

        if ($uidsToDelete === []) {
            return;
        }

        if ($emitSignals) {
            $this->emitBeforeMultipleItemsRemovedFromQueue($uidsToDelete);
        }

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->delete($this->table)->where($queryBuilder->expr()->in('uid', $uidsToDelete))->execute();

        if ($emitSignals) {
            $this->emitMultipleItemsRemovedFromQueue($uidsToDelete);
        }

        foreach ($uidsToDelete as $deletedUid) {
            if (array_key_exists($deletedUid, self::$identityMap)) {
                unset(self::$identityMap[$deletedUid]);
            }
        }
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *       Objects Factory        *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @param array $rows
     *
     * @return Item[]
     */
    protected function createObjectsFromRowArray(array $rows): array
    {
        $itemArray = [];
        foreach ($rows as $singleRow) {
            $itemArray[] = $this->createObject($singleRow);
        }

        return $itemArray;
    }

    /**
     * @param array $row
     *
     * @return Item
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
            $item->setLastUpdate($row['last_update']);
        }

        return self::$identityMap[$uid];
    }

    /**
     * @return ContextFactory
     */
    protected function getContextFactory(): ContextFactory
    {
        return GeneralUtility::makeInstance(ContextFactory::class);
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *           Signals            *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @param Item $item
     *
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitItemRemovedFromQueue(Item $item)
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'itemRemoved', [$item]);
    }

    /**
     * @param int[] $itemUids
     *
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitMultipleItemsRemovedFromQueue(array $itemUids)
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'multipleItemsRemoved', [$itemUids]);
    }

    /**
     * @param Item $item
     *
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitBeforeItemRemovedFromQueue(Item $item)
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'beforeItemRemoved', [$item]);
    }

    /**
     * @param int[] $itemUids
     *
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitBeforeMultipleItemsRemovedFromQueue(array $itemUids)
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'beforeMultipleItemsRemoved', [$itemUids]);
    }

    /*++++++++++++++++++++++++++++++*
     *                              *
     *           Helpers            *
     *                              *
     *++++++++++++++++++++++++++++++*/

    /**
     * @param string $fieldName
     * @param $value
     * @param int $quotingType
     * @return CompositeExpression
     */
    protected function getSimpleEqWhereClauseExpression(
        string $fieldName,
        $value,
        int $quotingType = PDO::PARAM_STR
    ): CompositeExpression {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->andWhere(
            $queryBuilder->expr()->eq($fieldName, $queryBuilder->quote($value, $quotingType))
        )->getQueryPart('where');
    }

    /**
     * @param string $fieldName
     * @param $value
     * @param int $quotingType
     * @return CompositeExpression
     */
    protected function getSimpleInWhereClauseExpression(
        string $fieldName,
        $value,
        int $quotingType = PDO::PARAM_STR
    ): CompositeExpression {
        $queryBuilder = $this->getQueryBuilder();
        $quotedValue = $quotingType >= 0 ? $queryBuilder->quote($value, $quotingType): $value;
        return $queryBuilder->andWhere(
            $queryBuilder->expr()->in($fieldName, $quotedValue)
        )->getQueryPart('where');
    }

    /**
     * @param array $data
     * @return CompositeExpression
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
     * @param ?CompositeExpression $whereClauseExpression
     * @param ?int $offset
     * @param ?int $limit
     * @return array
     * @throws DBALDriverException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    protected function fetchRecordsFromDatabase(
        CompositeExpression $whereClauseExpression = null,
        int $offset = null,
        int $limit = null
    ): array {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select(...$this->fields)->from($this->table);

        if (null !== $whereClauseExpression) {
            $queryBuilder->andWhere($whereClauseExpression);
        }

        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }
        return $queryBuilder
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @param Item $item
     * @return array
     * @throws AspectNotFoundException
     * @throws FileDoesNotExistException
     */
    protected function getEnrichedStandardData(Item $item): array
    {
        $data = [];
        $data['file'] = $item->getFile()->getUid();
        $data['last_update'] = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp') ?: time();
        $data['merge_id'] = $item->getMergeId();

        $data = array_merge($data, $item->getContext()->toArray());
        return array_intersect_key($data, array_flip($this->fields));
    }
}
