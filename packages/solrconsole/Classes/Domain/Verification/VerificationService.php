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

namespace ApacheSolrForTypo3\Solrconsole\Domain\Verification;

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Index\Queue\QueueItemRepository;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\SearchQuery;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\System\Solr\Service\SolrReadService;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The VerificationService is responsible to:
 *
 * * Check each indexing configuration of a site
 * * Check which documents are missing in Solr
 * * Check which documents are missing in TYPO3
 */
class VerificationService
{
    /**
     * @var ConnectionManager
     */
    protected ConnectionManager $connectionManager;

    /**
     * @var QueueItemRepository
     */
    protected QueueItemRepository $queueItemRepository;

    /**
     * @var ConnectionPool
     */
    protected ConnectionPool $connectionPool;

    /**
     * @param Site $site
     * @param array $languagesToCheck
     * @param array $configurationNamesToChecks
     * @param bool $fix
     * @return SiteVerificationResult
     */
    public function verifySite(Site $site, array $languagesToCheck = [], array $configurationNamesToChecks = [], $fix = false): SiteVerificationResult
    {
        /** @var SiteVerificationResult $result */
        $result = GeneralUtility::makeInstance(SiteVerificationResult::class);

        $solrConfiguration = $site->getSolrConfiguration();
        if (!$solrConfiguration->getEnabled()) {
            $result->addGlobalError('Site with rootPageId ' . (int)(($site->getRootPageId())) . ' is disabled');
            return $result;
        }

        $domain = $site->getDomain();
        if (empty($domain)) {
            $result->addGlobalError('Site with rootPageId ' . (int)(($site->getRootPageId())) . ' has no domain');
            return $result;
        }

        $languageIds = $site->getAvailableLanguageIds();
        if (empty($languagesToCheck)) {
            $languagesToCheck = array_merge([0], $languageIds);
        }

        if (empty($configurationNamesToChecks)) {
            $configurationNamesToChecks = $solrConfiguration->getEnabledIndexQueueConfigurationNames();
        }

        foreach ($configurationNamesToChecks as $configurationName) {
            $table = $solrConfiguration->getIndexQueueTypeOrFallbackToConfigurationName($configurationName);

            if ($table === 'sys_file_storage') {
                // handled by solfal
                continue;
            }
            /**
             * @var ConfigurationVerificationResult $configurationVerificationResult
             */
            $configurationVerificationResult = GeneralUtility::makeInstance(ConfigurationVerificationResult::class);
            $configurationVerificationResult->setConfigurationName($configurationName);
            $configurationVerificationResult->setTableName($table);

            foreach ($languagesToCheck as $language) {
                try {
                    $solrConnection = $this->getSolrConnectionForRootPageUidAndLanguage($site->getRootPageId(), (int)$language);
                    $readService = $solrConnection->getReadService();

                    $searchQuery = $this->getQueryForDocumentsInSolr($site, $domain, $table);

                    $solrUids = $this->getRecordUidsFromSolrIndex($readService, $searchQuery);
                    $configurationVerificationResult->setSolrUids($solrUids);
                    $typo3Uids = $this->getRecordUidsFromDatabaseTable($site, $solrConfiguration, $configurationName, $table, (int)$language);

                    $configurationVerificationResult->setTypo3Uids($typo3Uids);

                    $missingInTYPO3 = $configurationVerificationResult->getMissingInTYPO3();
                    $missingInTYPO3Count = count($missingInTYPO3);

                    $missingInSolr = $configurationVerificationResult->getMissingInSolr();
                    $missingInSolrCount = count($missingInSolr);

                    $indexQueueErrors = $this->getIndexQueueErrorCount($missingInSolr, $table);
                    $configurationVerificationResult->setIndexQueueErrors($indexQueueErrors);

                    if ($fix && $missingInTYPO3Count > 0) {
                        $this->deleteDocumentsFromSolrThatAreMissingInTYPO3($solrConnection, $missingInTYPO3);
                    }

                    if ($fix && $missingInSolrCount > 0) {
                        $this->queueItemsThatAreMissingInSolr($site, $missingInSolr, $table, $configurationName);
                    }
                } catch (NoSolrConnectionFoundException $ex) {
                    $configurationVerificationResult->addError("No solr connection found for site {$site->getRootPageId()}, language {$language}.");
                } catch (\Exception $ex) {
                    $configurationVerificationResult->addError("Solr connection could not be initialized for site {$site->getRootPageId()}, language {$language}.");
                }
            }

            $result->addConfigurationVerificationResult($configurationVerificationResult);
        }

        return $result;
    }

    /**
     * @param SolrReadService $readService
     * @param SearchQuery $searchQuery
     * @return array
     */
    protected function getRecordUidsFromSolrIndex(SolrReadService $readService, SearchQuery $searchQuery): array
    {
        $searchQuery->setFields('id,uid');
        $searchQuery->setRows(9999999);
        $response = $readService->search($searchQuery);
        $solrUids = [];
        if (isset($response->response->docs)) {
            foreach ($response->response->docs as $doc) {
                $solrId = $doc->id;
                $solrUid = $doc->uid;
                $solrUids[$solrId] = $solrUid;
            }
        }

        return $solrUids;
    }

    /**
     * @param array $missingInSolr
     * @param string $table
     * @return int
     */
    protected function getIndexQueueErrorCount(array $missingInSolr, string $table): int
    {
        $indexQueueErrors = 0;
        if (count($missingInSolr) <= 0) {
            return $indexQueueErrors;
        }
        foreach ($missingInSolr as $missingItemUid) {
            $items = $this->getQueueItemRepository()->findItemsByItemTypeAndItemUid($table, $missingItemUid);
            foreach ($items as $item) {
                $indexQueueErrors += (int)$item->getHasErrors();
            }
        }

        return $indexQueueErrors;
    }

    /**
     * @param SolrConnection $solrConnection
     * @param array $missingInTYPO3
     */
    protected function deleteDocumentsFromSolrThatAreMissingInTYPO3(SolrConnection $solrConnection, $missingInTYPO3): void
    {
        $writeService = $solrConnection->getWriteService();
        $rawQuery = '(id:' . implode(' OR id:', array_keys($missingInTYPO3)) . ')';
        $writeService->deleteByQuery($rawQuery);
    }

    /**
     * @param Site $site
     * @param $missingInSolr
     * @param $table
     * @param $configurationName
     */
    protected function queueItemsThatAreMissingInSolr(Site $site, $missingInSolr, $table, $configurationName): void
    {
        foreach ($missingInSolr as $itemUid) {
            $present = $this->getQueueItemRepository()->containsItem($table, $itemUid);
            if ($present) {
                $this->getQueueItemRepository()->updateExistingItemByItemTypeAndItemUidAndRootPageId($table, $itemUid, $site->getRootPageId(), time(), $configurationName);
            } else {
                $this->getQueueItemRepository()->add($table, $itemUid, $site->getRootPageId(), time(), $configurationName);
            }
        }
    }

    /**
     * @return QueueItemRepository
     */
    public function getQueueItemRepository(): QueueItemRepository
    {
        $this->queueItemRepository = $this->queueItemRepository ?? GeneralUtility::makeInstance(QueueItemRepository::class);

        return $this->queueItemRepository;
    }

    /**
     * @param QueueItemRepository $queueItemRepository
     */
    public function setQueueItemRepository(QueueItemRepository $queueItemRepository): void
    {
        $this->queueItemRepository = $queueItemRepository;
    }

    /**
     * @param Site $site
     * @param TypoScriptConfiguration $configuration
     * @param string $configurationName
     * @param string $table
     * @param int $language
     * @return array
     */
    protected function getRecordUidsFromDatabaseTable(Site $site, TypoScriptConfiguration $configuration, string $configurationName, string $table, int $language): array
    {
        $indexQueueConfiguration = $configuration->getIndexQueueConfigurationByName($configurationName);
        $pageUids = $this->getRelevantParentPageIds($site, $configurationName, $indexQueueConfiguration, $table);
        $typo3Uids = [];
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $result = $queryBuilder->select('uid')
            ->from($table)
            ->where($queryBuilder->expr()->in('sys_language_uid', [-1, $language]))
            ->andWhere($queryBuilder->expr()->in($table === 'pages' ? 'uid' : 'pid', $pageUids));
        $path = 'plugin.tx_solr.index.queue.' . $configurationName . '.additionalWhereClause';
        $additionalWhere = trim($configuration->getValueByPathOrDefaultValue($path, ''));
        if ($additionalWhere !== '') {
            $result->andWhere($additionalWhere);
        }
        if ($table === 'pages') {
            // Exclude shotcuts, pointing on pages in same tree or additionalPageIds.
            $result->andWhere(sprintf(
                'NOT (doktype=4 AND (shortcut = 0 OR shortcut IN(%s)))',
                implode(',', $pageUids)
            ));
        }
        $result = $result->executeQuery()->fetchAllAssociative();

        foreach ($result as $entry) {
            $typo3Uid = $entry['uid'];
            $typo3Uids[] = $typo3Uid;
        }
        return array_unique($typo3Uids);
    }

    /**
     * @param ConnectionPool $connectionPool
     */
    public function setConnectionPool(ConnectionPool $connectionPool): void
    {
        $this->connectionPool = $connectionPool;
    }

    /**
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        if (!$this->connectionPool instanceof ConnectionPool) {
            $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        }

        return $this->connectionPool;
    }

    /**
     * @param int $rootPageUid
     * @param int $language
     * @return SolrConnection
     * @throws NoSolrConnectionFoundException
     */
    protected function getSolrConnectionForRootPageUidAndLanguage(int $rootPageUid, int $language = 0): SolrConnection
    {
        return $this->getConnectionManager()->getConnectionByPageId($rootPageUid, $language);
    }

    /**
     * @return ConnectionManager
     */
    public function getConnectionManager(): ConnectionManager
    {
        if (!$this->connectionManager instanceof ConnectionManager) {
            $this->connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
        }
        return $this->connectionManager;
    }

    /**
     * @param ConnectionManager $connectionManager
     */
    public function setConnectionManager(ConnectionManager $connectionManager): void
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Gets the pages uids in a site plus additional pages that may have been configured.
     *
     * @param Site $site
     * @param array $indexQueueConfiguration
     * @param string $table
     * @return array A (sorted) array of page IDs in a site
     */
    protected function getRelevantParentPageIds(Site $site, string $configurationName, array $indexQueueConfiguration, $table): array
    {
        $pages = $site->getPages(null, $configurationName);

        // when we have a pages table the pid of the rootPage need to be added as well because when we look for pid's
        // this needs to be retrieved to fetch the root page
        if ($table === 'pages') {
            $pages[] = $site->getRootPageId();
        }

        $additionalPageIds = [];
        if (!empty($indexQueueConfiguration['additionalPageIds'])) {
            $additionalPageIds = GeneralUtility::intExplode(',', $indexQueueConfiguration['additionalPageIds']);
        }

        $pages = array_merge($pages, $additionalPageIds);
        sort($pages, SORT_NUMERIC);

        return $pages;
    }

    /**
     * @param Site $site
     * @param $domain
     * @param $table
     * @return SearchQuery
     */
    protected function getQueryForDocumentsInSolr(Site $site, $domain, $table): SearchQuery
    {
        $searchQuery = new SearchQuery();
        $solrQuery = "(site:{$domain} AND type:{$table} AND siteHash:{$site->getSiteHash()})";
        $searchQuery->setQuery($solrQuery)->setStart(0)->setRows(9999999);
        return $searchQuery;
    }
}
