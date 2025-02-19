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
use ApacheSolrForTypo3\Solr\Domain\Site\Exception\UnexpectedTYPO3SiteInitializationException;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrfal\Context\PageContext;
use ApacheSolrForTypo3\Solrfal\Exception\Detection\InvalidHookException as InvalidDetectionHookException;
use ApacheSolrForTypo3\Solrfal\Exception\Service\InvalidHookException as InvalidServiceHookException;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class PageContextDetector
 */
class PageContextDetector extends AbstractRecordDetector
{
    /**
     * @param string $indexingConfigurationName
     * @param bool $indexQueueForConfigurationNameIsInitialized
     * @inheritDoc
     *
     * @throws DBALException
     */
    public function initializeQueue(
        string $indexingConfigurationName,
        ?bool $indexQueueForConfigurationNameIsInitialized = false,
    ): bool {
        // skip if context disabled or not pages related, this is no initialisation failure
        if (!$this->isIndexingEnabledForContext('page') || $indexingConfigurationName !== 'pages') {
            return true;
        }

        try {
            $this->getItemRepository()->removeBySiteAndContext($this->site, 'page');
        } catch (DBALException) {
            return false;
        }

        return true;
    }

    /**
     * Add detected files to given page.
     *
     * @param TypoScriptFrontendController $responsibleTsfe Responsible TSFE
     * @param Rootline $pageAccessRootline Access rootline of PID
     * @param int[] $allowedFileUids The list of allowed file UIDs
     * @param array<string, string|int|bool>[] $contentElements The content elements records
     * @param int[] $successfulUids The list of successful file UIDs
     *
     * @return array|int[]
     *
     * @throws DBALException
     * @throws InvalidDetectionHookException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws UnknownLinkHandlerException
     */
    public function addDetectedFilesToPage(
        TypoScriptFrontendController $responsibleTsfe,
        Rootline $pageAccessRootline,
        array $allowedFileUids = [],
        array $contentElements = [],
        array $successfulUids = [],
    ): array {
        if (!$this->isIndexingEnabledForContext('page')) {
            return [];
        }
        $contentTypeFieldMapping = $this->getContentElementTypeToFieldMapping();

        $this->logger->info('Adding trigger indexing files for page ' . $responsibleTsfe->id, $contentTypeFieldMapping);
        $this->logger->info('Files with calls on "getPublicUrl" ', $allowedFileUids);
        $this->logger->info('Content element count:' . count($contentElements));

        // as we cannot rely on the page access rootline for files in the pageContext
        // we create an empty rootline for files, which will be completed during
        // the indexing process
        $fileAccessRootline = GeneralUtility::makeInstance(Rootline::class);

        /** @var array{uid: int, pid: int, CType: string} $singleElement */
        foreach ($contentElements as $singleElement) {
            $contentType = $singleElement['CType'] ?? '';
            if (array_key_exists($contentType, $contentTypeFieldMapping)) {
                $indexableFields = GeneralUtility::trimExplode(',', $contentTypeFieldMapping[$contentType]);

                foreach ($indexableFields as $field) {
                    $this->logger->info('Indexing field ' . $field);
                    $attachedFileUids = $this->getFileAttachmentResolver()->detectFilesInField('tt_content', $field, $singleElement);
                    $this->logger->info('Found-Files: ' . implode(', ', $attachedFileUids));

                    $linkedFiles = array_intersect($attachedFileUids, $allowedFileUids);

                    $this->logger->info('Found files which have been linked: ' . implode(', ', $linkedFiles));

                    if ($linkedFiles !== []) {
                        $context = GeneralUtility::makeInstance(
                            PageContext::class,
                            $this->site,
                            $fileAccessRootline,
                            'tt_content',
                            $field,
                            $singleElement['uid'],
                            $responsibleTsfe->id,
                            $responsibleTsfe->getLanguage()->getLanguageId()
                        );
                        $this->logger->info('Context created');

                        $successfulUids = $this->addFileUidsToQueue($successfulUids, $linkedFiles, $context);
                    }
                }
            }
        }

        // Find attachments
        $this->logger->info('isPageAttachmentsEnabled:' . $this->isPageAttachmentsEnabled());
        /** @var array{uid: int, pid: int} $responsiblePageRecord */
        $responsiblePageRecord = $responsibleTsfe->page;
        if ($this->isPageAttachmentsEnabled()) {
            foreach ($this->getPageAttachmentFields() as $field) {
                $this->logger->info('Indexing field pages.' . $field);
                $attachedFileUids = $this->getFileAttachmentResolver()->detectFilesInField(
                    'pages',
                    $field,
                    $responsiblePageRecord,
                );
                $this->logger->info('Found-Files: ' . implode(', ', $attachedFileUids));

                $linkedFiles = array_intersect($attachedFileUids, $allowedFileUids);

                $this->logger->info('Found files which have been linked: ' . implode(', ', $linkedFiles));

                if ($linkedFiles !== []) {
                    $context = GeneralUtility::makeInstance(
                        PageContext::class,
                        $this->site,
                        $fileAccessRootline,
                        'pages',
                        $field,
                        $responsibleTsfe->id,
                        $responsiblePageRecord['pid'],
                        $responsibleTsfe->getLanguage()->getLanguageId()
                    );
                    $this->logger->info('Context created');

                    $successfulUids = $this->addFileUidsToQueue($successfulUids, $linkedFiles, $context);
                }
            }
        }

        $this->getItemRepository()->removeOldEntriesInPageContext($this->site, $responsibleTsfe->id, $responsibleTsfe->getLanguage()->getLanguageId(), ...$successfulUids);

        $forcedFiles = $this->getForcedFilesForPage($responsibleTsfe, $this->site);
        $context = GeneralUtility::makeInstance(
            PageContext::class,
            $this->site,
            $fileAccessRootline,
            'pages',
            '',
            $responsibleTsfe->id,
            $responsiblePageRecord['pid'],
            $responsibleTsfe->getLanguage()->getLanguageId()
        );
        $successfulUids = $this->addFileUidsToQueue($successfulUids, $forcedFiles, $context);

        $this->logger->info('All files: ' . json_encode($successfulUids));
        return $successfulUids;
    }

    /**
     * @param int[] $successfulUids array with successful file UIDs
     * @param int[] $linkedFiles array with linked file UIDs
     * @param PageContext $context Current Page-Context definition
     * @return int[] Merged list of $successfulUids and $linkedFiles.
     *               **Note:** only $linkedFiles, for which the file index queue entry could be created
     */
    protected function addFileUidsToQueue(
        array $successfulUids,
        array $linkedFiles,
        PageContext $context
    ): array {
        foreach ($linkedFiles as $fileUid) {
            if ($this->createIndexQueueEntryForFile($fileUid, $context)) {
                $this->logger->info('Indexed-Filed: ' . $fileUid);

                $successfulUids[] = $fileUid;
            }
        }
        return $successfulUids;
    }

    /**
     * Returns the referenced pageContextDetectorAspects
     *
     * @return PageContextDetectorAspectInterface[]
     *
     * @throws InvalidDetectionHookException
     */
    protected function getPageContextDetectorAspects(): array
    {
        $result = [];
        $fileAttachmentResolverAspectReferences = (array)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['PageContextDetectorAspectInterface'] ?? []);

        foreach ($fileAttachmentResolverAspectReferences as $fileAttachmentResolverAspectReference) {
            $fileAttachmentResolverAspect = GeneralUtility::makeInstance($fileAttachmentResolverAspectReference);
            if (!$fileAttachmentResolverAspect instanceof PageContextDetectorAspectInterface) {
                throw new InvalidDetectionHookException('Invalid hook definition for PageContextDetectorAspectInterface', 1661774407);
            }
            $result[] = $fileAttachmentResolverAspect;
        }

        return $result;
    }

    /**
     * @return int[]
     *
     * @throws InvalidDetectionHookException
     */
    protected function getForcedFilesForPage(TypoScriptFrontendController $page, Site $site): array
    {
        $fileAttachmentResolverAspects = $this->getPageContextDetectorAspects();
        if (count($fileAttachmentResolverAspects) == 0) {
            return [];
        }
        $fileUids = [];

        // we have valid hooks and trigger them
        foreach ($fileAttachmentResolverAspects as $fileAttachmentResolverAspect) {
            $fileUids = $fileAttachmentResolverAspect->addForcedFilesOnPage($fileUids, $page, $site);
        }

        return $fileUids;
    }

    /**
     * Create Index Queue entry for a concrete file uid and it's context
     */
    protected function createIndexQueueEntryForFile(
        int $fileUid,
        PageContext $context,
    ): bool {
        try {
            $file = $this->getResourceFactory()->getFileObject($fileUid);
            $this->logger->info('Got File Object for identifier ' . $file->getCombinedIdentifier());
            if ($this->isAllowedFileExtension($file)) {
                $this->logger->info('File extension allowed ' . $file->getExtension());
                $indexQueueItem = $this->createQueueItem($file, $context);
                if (!$this->getItemRepository()->exists($indexQueueItem)) {
                    $this->getItemRepository()->add($indexQueueItem);
                } else {
                    $this->getItemRepository()->update($indexQueueItem);
                }
            } else {
                $this->logger->info('File extension is not allowed: ' . $file->getExtension());
                return false;
            }
        } catch (ResourceDoesNotExistException) {
            $this->logger->error('File not found: ' . $fileUid);
            return false;
        } catch (Throwable) {
            $this->logger->error('Unknown exception while loading file: ' . $fileUid);
            return false;
        }

        return true;
    }

    /**
     * Get the configured mapping, which fields of which content-element type should be indexed
     *
     * @return array<string, string>
     */
    protected function getContentElementTypeToFieldMapping(): array
    {
        return $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.contentElementTypes.');
    }

    /**
     * Check if the content element type is configured to be allowed for indexing
     */
    protected function isContentElementTypeAllowed(string $contentType): bool
    {
        $contentTypeFieldMapping = $this->getContentElementTypeToFieldMapping();
        return array_key_exists($contentType, $contentTypeFieldMapping);
    }

    protected function isPageAttachmentsEnabled(): bool
    {
        $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.');

        return !empty($config['attachments']);
    }

    /**
     * Get pageContext.attachments.fields
     *
     * @return string[]
     */
    protected function getPageAttachmentFields(): array
    {
        $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.attachments.');

        return GeneralUtility::trimExplode(',', $config['fields'] ?? '');
    }

    /**
     * Check if the file extension is configured to be allowed for indexing
     */
    protected function isAllowedFileExtension(File $file): bool
    {
        $pageConfig = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.');
        $extensions = isset($pageConfig['fileExtensions']) ? strtolower(trim($pageConfig['fileExtensions'])) : '';
        if ($extensions === '*') {
            return true;
        }
        $allowedFileExtensions = GeneralUtility::trimExplode(',', $extensions);
        return in_array($file->getExtension(), $allowedFileExtensions);
    }

    /**
     * @noinspection PhpUnused
     */
    public function recordCreated(string $table, int $uid): void
    {
        // nothing to do since editing page already.
        // already triggers re-indexing.
    }

    /**
     * @throws AspectNotFoundException
     * @throws DBALException
     */
    public function recordUpdated(string $table, int $uid): void
    {
        if ($table === 'pages') {
            $page = BackendUtility::getRecord('pages', $uid);
            if ($page['hidden']) {
                $this->getItemRepository()->removeOldEntriesInPageContext($this->site, $uid);
            }
        } elseif ($table === 'tt_content') {
            $contentElement = BackendUtility::getRecord('tt_content', $uid);
            if ($contentElement['hidden'] || !$this->isContentElementTypeAllowed($contentElement['CType'])) {
                $this->getItemRepository()->removeByTableAndUidInContext('page', $this->site, 'tt_content', $uid);
            }
        } elseif ($table === 'sys_file_reference') {
            $fileReference = $this->getFileReferenceObject($uid);
            if ($fileReference->getReferenceProperty('tablenames') === 'tt_content' && $fileReference->getReferenceProperty('hidden')) {
                $this->getItemRepository()->removeByTableAndUidInContext(
                    'page',
                    $this->site,
                    'tt_content',
                    $fileReference->getReferenceProperty('uid_foreign'),
                    $fileReference->getReferenceProperty('uid_local')
                );
            }
        } elseif ($table === 'sys_file_metadata') {
            $fileRecord = BackendUtility::getRecord($table, $uid, 'file', '', false);
            $file = $this->getFile($fileRecord['file']);
            if ($file === null) {
                $this->logger->error(vsprintf(
                    'File Instance with uid "%s", which is referenced by "sys_file_metadata" with uid "%s", could not be fetched.',
                    [
                        $fileRecord['file'],
                        $uid,
                    ]
                ));
                return;
            }
            $this->getItemRepository()->markFileUpdated($file->getUid(), ['context' => 'page']);
            $this->logger->info('Metadata for file ' . $fileRecord['file'] . ' updated, marking queue-item for re-indexing');
        }
    }

    /**
     * @throws DBALException
     */
    public function recordDeleted(string $table, int $uid): void
    {
        if ($table === 'pages') {
            $this->getItemRepository()->removeOldEntriesInPageContext($this->site, $uid);
        } elseif ($table === 'tt_content') {
            $this->getItemRepository()->removeByTableAndUidInContext('page', $this->site, 'tt_content', $uid);
        } elseif ($table == 'sys_file_reference') {
            // get reference and try to delete files of referencing records
            // Note that processCmdmap_preProcess must be used to call recordDeleted, otherwise
            // the file reference will not be available

            $fileReference = $this->getFileReferenceObject($uid);
            if ($fileReference->getReferenceProperty('tablenames') == 'tt_content') {
                $this->getItemRepository()->removeByTableAndUidInContext(
                    'page',
                    $this->site,
                    'tt_content',
                    $fileReference->getReferenceProperty('uid_foreign'),
                    $fileReference->getReferenceProperty('uid_local')
                );
            }
        }
    }

    /**
     * Handles updates on sys_file entries
     *
     * @throws DBALException
     * @throws UnexpectedTYPO3SiteInitializationException
     * @throws AspectNotFoundException
     */
    public function fileIndexRecordUpdated(
        string $table,
        int $uid,
    ): void {
        // skip if record context is not enabled or file not allowed
        if (!$this->isIndexingEnabledForContext('page')) {
            return;
        }

        if (
            $table == 'sys_file'
            && ($file = $this->getFile($uid))
            && $this->isAllowedFileExtension($file)
        ) {
            $referencedPages = $this->getReferencedPages($uid);
            if ($referencedPages) {
                $indexQueue = $this->getIndexQueue();
                foreach ($referencedPages as $pageUid) {
                    $indexQueue->updateItem('pages', $pageUid, GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'));
                }
            }
        }
    }

    /**
     * Returns referenced pages that have to be updated
     *
     * @return int[]
     *
     * @throws DBALException
     */
    protected function getReferencedPages(int $fileUid): array
    {
        $referencedPages = [];
        $referenceIndexRepository = $this->getReferenceIndexEntryRepository();

        // get and process the references
        // restrict to pages, tt_content and sys_file_reference
        $references = $referenceIndexRepository->findByReferenceRecord(
            'sys_file',
            $fileUid,
            [],
            [
                'pages',
                'tt_content',
                'sys_file_reference',
            ]
        );
        foreach ($references as $reference) {
            // try to get right reference to index record if reference refers to sys_file_reference
            if ($reference->getTableName() == 'sys_file_reference') {
                // get record reference if this is only a reference to sys_file_reference
                $reference = $referenceIndexRepository->findOneByReferenceIndexEntry($reference);
                if (!$reference) {
                    continue;
                }
            }

            switch ($reference->getTableName()) {
                case 'pages':
                    $referencedPages[] = $reference->getRecordUid();
                    break;

                case 'tt_content':
                    if ($record = $reference->getRecord()) {
                        $referencedPages[] = (int)$record['pid'];
                    }
                    break;

                default:
            }
        }

        return array_unique($referencedPages);
    }

    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}
