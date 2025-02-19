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

namespace ApacheSolrForTypo3\Solrfal\Detection;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Index\Queue\QueueItemRepository as SolrQueueItemRepository;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Exception\Exception as SolrFrontendEnvironmentException;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use ApacheSolrForTypo3\Solrfal\Domain\Repository\ReferenceIndexEntryRepository;
use ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Service\FileAttachmentResolver;
use Doctrine\DBAL\Exception as DBALException;
use PDO;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractRecordDetector
 */
abstract class AbstractRecordDetector implements RecordDetectionInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected readonly Site $site,
        protected ?TypoScriptConfiguration $siteConfiguration = null
    ) {
        $this->siteConfiguration = is_null($this->siteConfiguration) ? $site->getSolrConfiguration() : $siteConfiguration;
    }

    /**
     * Checks if the Indexing is Enabled
     */
    protected function isIndexingEnabledForContext(string $context): bool
    {
        $contextConfig = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.');
        return !empty($contextConfig[$context . 'Context']);
    }

    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    protected function getSolrQueueItemRepository(): SolrQueueItemRepository
    {
        return GeneralUtility::makeInstance(SolrQueueItemRepository::class);
    }

    protected function getStorageRepository(): StorageRepository
    {
        return GeneralUtility::makeInstance(StorageRepository::class);
    }

    protected function getFileAttachmentResolver(): FileAttachmentResolver
    {
        return GeneralUtility::makeInstance(FileAttachmentResolver::class);
    }

    protected function getReferenceIndexEntryRepository(): ReferenceIndexEntryRepository
    {
        return GeneralUtility::makeInstance(ReferenceIndexEntryRepository::class);
    }

    protected function getIndexQueue(): Queue
    {
        return GeneralUtility::makeInstance(Queue::class);
    }

    protected function getFile(int $fileUid): ?File
    {
        $file = null;
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $file = $resourceFactory->getFileObject($fileUid);
        } catch (ResourceDoesNotExistException) {
            $this->logger->error('File not found: ' . $fileUid);
        } catch (Throwable $e) {
            $this->logger->error(
                'Unknown exception while loading file: ' . $fileUid . PHP_EOL .
                'Code: ' . $e->getCode() . PHP_EOL .
                'Message: ' . $e->getMessage()
            );
        }

        return $file;
    }

    /**
     * Returns a file reference object
     *
     * We use this own method since ResourceFactory caches the
     * file reference objects, and we cannot be sure that this object is up-to-date
     */
    protected function getFileReferenceObject(int $fileReferenceUid): ?FileReference
    {
        $fileReference = null;

        try {
            $fileReferenceData = BackendUtility::getRecord('sys_file_reference', $fileReferenceUid);
            $fileReference = $this->getResourceFactory()->createFileReferenceObject($fileReferenceData);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $fileReference;
    }

    /**
     * Creates a new queue item
     */
    protected function createQueueItem(File $file, ContextInterface $context): Item
    {
        /** @var Item $item */
        $item = GeneralUtility::makeInstance(Item::class, $file->getUid(), $context);

        // a file should be unique per file, language and site
        $mergeId =  DocumentFactory::SOLR_TYPE . '/' .
            $file->getUid() . '/' .
            $context->getLanguage() . '/' .
            $context->getSite()->getRootPageId();

        $item->setMergeId($mergeId);

        return $item;
    }

    /**
     * Handles new sys_file entries
     */
    public function fileIndexRecordCreated(string $table, int $uid): void
    {
        // handle creation of sys_file records, presumably only relevant for storage context
    }

    /**
     * Handles deletions of sys_file entries
     *
     * @throws DBALException
     */
    public function fileIndexRecordDeleted(string $table, int $uid): void
    {
        // TODO: check if action is required, since file deletion is also taken care of via Slot postFileDelete
        $this->getItemRepository()->removeByFileUid($uid);
    }

    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }

    protected function getAccessRootlineByPageId(int $pageId): Rootline
    {
        return Rootline::getAccessRootlineByPageId($pageId);
    }

    /**
     * @throws SiteNotFoundException
     * @throws SolrFrontendEnvironmentException
     * @throws DBALException
     *
     * @param int $languageId The language Uid
     * @param string $tableName The table name to resolve core context from
     * @param array{uid: int, pid: int} $record The record to resolve core context from
     *
     * @todo: Most probably the "mount point" feature does not work here.
     */
    protected function getCoreContextForLanguageByTableNameAndRecord(
        int $languageId,
        string $tableName,
        array $record,
    ): ?Context {
        $pidToUse = $record['pid'];
        if ($tableName === 'pages') {
            $pidToUse = $record['uid'];
        }
        /** @var Tsfe $tsfeFactory */
        $tsfeFactory = GeneralUtility::makeInstance(Tsfe::class);
        $tsfe = $tsfeFactory->getTsfeByPageIdAndLanguageId($pidToUse, $languageId);
        $context = $tsfe?->getContext();
        if ($context === null) {
            return null;
        }

        // Collect user restrictions relevant for overlay. This is required as we otherwise
        // cannot create an overlay if just the overlay is restricted.
        $context->setAspect(
            'frontend.user',
            GeneralUtility::makeInstance(
                UserAspect::class,
                null,
                $this->getUserGroupsToSimulate($tableName, $record['uid'])
            )
        );

        return $context;
    }

    /**
     * Checks if frontend user aspect must be simulated
     */
    protected function isSimulatedFrontendUserRequired(string $tableName): bool
    {
        if (!empty($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'] ?? null)
            && !empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField']  ?? null)
            && !empty($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? null)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determines the usergroups that must be simulated
     *
     * @return int[]
     *
     * @throws DBALException
     */
    protected function getUserGroupsToSimulate(
        string $tableName,
        int $recordUid
    ): array {
        if (!$this->isSimulatedFrontendUserRequired($tableName)) {
            return [];
        }

        $queryBuilder = $this->getQueryBuilder($tableName);
        $records = $queryBuilder
            ->select($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'])
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->neq(
                    $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'],
                    $queryBuilder->createNamedParameter('')
                ),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($recordUid, PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'],
                        $queryBuilder->createNamedParameter($recordUid, PDO::PARAM_INT)
                    )
                )
            )
            ->executeQuery()
            ->fetchAllNumeric();

        $userGroups = [];
        foreach ($records as $record) {
            $userGroups = array_merge(
                $userGroups,
                GeneralUtility::intExplode(',', $record[0])
            );
        }

        return array_unique($userGroups);
    }

    protected function getQueryBuilder(string $tableName): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
    }
}
