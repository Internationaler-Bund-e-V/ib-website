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
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\Util;
use ApacheSolrForTypo3\Solrfal\Context\PageContext;
use ApacheSolrForTypo3\Solrfal\Exception\Detection\InvalidHookException;
use Exception;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class PageContextDetector
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class PageContextDetector extends AbstractRecordDetector
{

    /**
     * @param array $initializationStatus
     */
    public function initializeQueue(array $initializationStatus)
    {
        // files will be detected on frontend indexing - so remove all entries
        if (empty($initializationStatus) || (array_key_exists('pages', $initializationStatus) && $initializationStatus['pages'] == true)) {
            $this->getItemRepository()->removeBySiteAndContext($this->site, 'page');
        }
    }

    /**
     * @param TypoScriptFrontendController $page
     * @param Rootline $pageAccessRootline
     * @param int[] $allowedFileUids
     * @param array $contentElements
     * @param int[] $successfulUids
     * @return int[] $successfulUids
     * @throws InvalidHookException
     */
    public function addDetectedFilesToPage(TypoScriptFrontendController $page, Rootline $pageAccessRootline, array $allowedFileUids = [], array $contentElements = [], array $successfulUids = [])
    {
        if (!$this->isIndexingEnabledForContext('page')) {
            return [];
        }
        $contentTypeFieldMapping = $this->getContentElementTypeToFieldMapping();

        $this->logger->info('Adding trigger indexing files for page ' . $page->id, $contentTypeFieldMapping);
        $this->logger->info('Files with calls on "getPublicUrl" ', $allowedFileUids);
        $this->logger->info('Content element count:' . count($contentElements));

        // as we cannot rely on the page access rootline for files in the pageContext
        // we create an empty rootline for files, which will be completed during
        // the indexing process
        $fileAccessRootline = GeneralUtility::makeInstance(Rootline::class);

        foreach ($contentElements as $singleElement) {
            $contentType = $singleElement['CType'];
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
                            $page->id,
                            'tt_content',
                            $field,
                            $singleElement['uid'],
                            Util::getLanguageUid()
                        );
                        $this->logger->info('Context created');

                        $successfulUids = $this->addFileUidsToQueue($successfulUids, $linkedFiles, $context);
                    }
                }
            }
        }

        // Find attachments
        $this->logger->info('isPageAttachmentsEnabled:' . $this->isPageAttachmentsEnabled());
        if ($this->isPageAttachmentsEnabled()) {
            foreach ($this->getPageAttachmentFields() as $field) {
                $table = Util::getLanguageUid() === 0 ? 'pages' : 'pages_language_overlay';
                $this->logger->info('Indexing field ' . $table . '.' . $field);
                $attachedFileUids = $this->getFileAttachmentResolver()->detectFilesInField($table, $field, $page->page);
                $this->logger->info('Found-Files: ' . implode(', ', $attachedFileUids));

                $linkedFiles = array_intersect($attachedFileUids, $allowedFileUids);

                $this->logger->info('Found files which have been linked: ' . implode(', ', $linkedFiles));

                if ($linkedFiles !== []) {
                    $context = GeneralUtility::makeInstance(
                        PageContext::class,
                        $this->site,
                        $fileAccessRootline,
                        $page->id,
                        'pages',
                        $field,
                        $page->id,
                        Util::getLanguageUid()
                    );
                    $this->logger->info('Context created');

                    $successfulUids = $this->addFileUidsToQueue($successfulUids, $linkedFiles, $context);
                }
            }
        }

        $this->getItemRepository()->removeOldEntriesInPageContext($this->site, (int)$page->id, Util::getLanguageUid(), ...$successfulUids);

        $forcedFiles = $this->getForcedFilesForPage($page, $this->site);
        $context = GeneralUtility::makeInstance(
            PageContext::class,
            $this->site,
            $fileAccessRootline,
            $page->id,
            'pages',
            '',
            $page->id,
            Util::getLanguageUid()
        );
        $successfulUids = $this->addFileUidsToQueue($successfulUids, $forcedFiles, $context);

        $this->logger->info('All files: ' . json_encode($successfulUids));
        return $successfulUids;
    }

    /**
     * @param array $successfulUids
     * @param $linkedFiles
     * @param $context
     * @return array
     */
    protected function addFileUidsToQueue(array $successfulUids, $linkedFiles, $context): array
    {
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
     * @throws InvalidHookException
     * @return PageContextDetectorAspectInterface[]
     */
    protected function getPageContextDetectorAspectAspects()
    {
        $hasFileAttachmentResolverProcessor = !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['PageContextDetectorAspectInterface']);
        if (!$hasFileAttachmentResolverProcessor) {
            return [];
        }

        $result = [];
        $fileAttachmentResolverAspectReferences = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['PageContextDetectorAspectInterface'];

        foreach ($fileAttachmentResolverAspectReferences as $fileAttachmentResolverAspectReference) {
            $fileAttachmentResolverAspect = GeneralUtility::makeInstance($fileAttachmentResolverAspectReference);
            if (!$fileAttachmentResolverAspect instanceof PageContextDetectorAspectInterface) {
                throw new InvalidHookException('Invalid hook definition for PageContextDetectorAspectInterface', 1661774407);
            }
            $result[] = $fileAttachmentResolverAspect;
        }

        return $result;
    }

    /**
     * @param TypoScriptFrontendController $page
     * @param Site $site
     * @return array
     * @throws InvalidHookException
     */
    protected function getForcedFilesForPage(TypoScriptFrontendController $page, Site $site)
    {
        $fileAttachmentResolverAspects = $this->getPageContextDetectorAspectAspects();
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
     *
     * @param int $fileUid
     * @param PageContext $context
     *
     * @return bool
     */
    protected function createIndexQueueEntryForFile($fileUid, PageContext $context)
    {
        try {
            $file = $this->getResourceFactory()->getFileObject((int)$fileUid);
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
        } catch (ResourceDoesNotExistException $e) {
            $this->logger->error('File not found: ' . $fileUid);
            return false;
        } catch (Exception $e) {
            $this->logger->error('Unknown exception while loading file: ' . $fileUid);
            return false;
        }

        return true;
    }

    /**
     * Get the configured mapping, which fields of which content-element type should be indexed
     *
     * @return array
     */
    protected function getContentElementTypeToFieldMapping()
    {
        return $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.contentElementTypes.', []);
    }

    /**
     * Check if the content element type is configured to be allowed for indexing
     *
     * @param string $contentType
     * @return bool
     */
    protected function isContentElementTypeAllowed($contentType)
    {
        $contentTypeFieldMapping = $this->getContentElementTypeToFieldMapping();
        return array_key_exists($contentType, $contentTypeFieldMapping);
    }

    /**
     * @return bool
     */
    protected function isPageAttachmentsEnabled(): bool
    {
        $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.', []);

        return !empty($config['attachments']);
    }

    /**
     * Get pageContext.attachments.fields
     *
     * @return string[]
     */
    protected function getPageAttachmentFields(): array
    {
        $config = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.attachments.', []);

        return GeneralUtility::trimExplode(',', $config['fields'] ?? '');
    }

    /**
     * Check if the file extension is configured to be allowed for indexing
     *
     * @param File $file
     *
     * @return bool
     */
    protected function isAllowedFileExtension(File $file)
    {
        $pageConfig = $this->siteConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.', []);
        $extensions = isset($pageConfig['fileExtensions']) ? strtolower(trim($pageConfig['fileExtensions'])) : '';
        if ($extensions === '*') {
            return true;
        }
        $allowedFileExtensions = GeneralUtility::trimExplode(',', $extensions);
        return in_array($file->getExtension(), $allowedFileExtensions);
    }

    /**
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function recordCreated(string $table, int $uid)
    {
        // nothing to do since editing page already
        // already triggers re-indexing.
    }

    /**
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function recordUpdated(string $table, int $uid)
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
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function recordDeleted(string $table, int $uid)
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
     * @param string $table
     * @param int $uid
     *
     * @noinspection PhpUnused
     */
    public function fileIndexRecordUpdated(string $table, int $uid)
    {
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
     * @param int $fileUid
     *
     * @return int[]
     */
    protected function getReferencedPages($fileUid)
    {
        $referencedPages = [];
        $referenceIndexRepository = $this->getReferenceIndexEntryRepository();

        // get and process references
        // restrict to pages, tt_content and sys_file_reference
        $references = $referenceIndexRepository->findByReferenceRecord('sys_file', $fileUid, [], ['pages', 'tt_content', 'pages_language_overlay', 'sys_file_reference']);
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

    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}
