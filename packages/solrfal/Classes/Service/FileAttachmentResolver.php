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

namespace ApacheSolrForTypo3\Solrfal\Service;

use ApacheSolrForTypo3\Solrfal\Exception\Service\InvalidHookException;
use ApacheSolrForTypo3\Solrfal\System\Links\FileLinkExtractionService;
use Exception;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileCollectionRepository;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Resource\FileCollector;

/**
 * Class FileAttachmentResolver
 */
class FileAttachmentResolver implements SingletonInterface
{
    /**
     * Detects attachments of an single field
     *
     * @param string $tableName
     * @param string $fieldName
     * @param array $record
     *
     * @return int[]
     * @throws InvalidHookException
     */
    public function detectFilesInField($tableName, $fieldName, $record): array
    {
        if (!isset($GLOBALS['TCA'][$tableName]) || !isset($GLOBALS['TCA'][$tableName]['columns'][$fieldName]) || empty($record[$fieldName])) {
            return [];
        }
        $fieldConfiguration = $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config'];
        $fileUids = [];
        switch ($fieldConfiguration['type']) {
            case 'input':
                // single line and multi line text fields behave the same
            case 'text':
                $fileUids = $this->detectFilesInTextField($fieldName, $record);
                break;
            case 'group':
                $fileUids = $this->detectFilesInGroupField($tableName, $fieldName, $record, $fieldConfiguration);
                break;
            case 'select':
                // todo no use case existent currently
                break;
            case 'inline':
                $fileUids = $this->detectFilesInInlineField($tableName, $fieldName, $record, $fieldConfiguration);
                break;
            case 'flex':
                // todo: files in flexforms are not supported yet
                break;
            default:
                break;
        }

        $fileUids = $this->applyPostDetectFilesInFieldHook($fileUids, $tableName, $fieldName, $record);
        return $fileUids;
    }

    /**
     * Calls postDetectFilesInField on the configured FileAttachmentResolverAspects:
     *
     * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect']
     *
     * @param $fileUids
     * @param string $tableName
     * @param string $fieldName
     * @param array $record
     *
     * @return int[]
     * @throws InvalidHookException
     */
    protected function applyPostDetectFilesInFieldHook($fileUids, $tableName, $fieldName, $record): array
    {
        $fileAttachmentResolverAspects = $this->getFileAttachmentResolverAspects();
        if (count($fileAttachmentResolverAspects) == 0) {
            return $fileUids;
        }

        // we have valid hooks and trigger them
        foreach ($fileAttachmentResolverAspects as $fileAttachmentResolverAspect) {
            $fileUids = $fileAttachmentResolverAspect->postDetectFilesInField($fileUids, $tableName, $fieldName, $record, $this);
        }

        // check files
        $fileUids = $this->checkFiles($fileUids);

        return $fileUids;
    }

    /**
     * Returns the array of the registered class references of the ResolverAspects or an empty array, when non
     * is registered.
     *
     * @throws InvalidHookException
     * @return FileAttachmentResolverAspectInterface[]
     */
    protected function getFileAttachmentResolverAspects(): array
    {
        $hasFileAttachmentResolverProcessor = !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect']);
        if (!$hasFileAttachmentResolverProcessor) {
            return [];
        }

        $result = [];
        $fileAttachmentResolverAspectReferences = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect'];

        foreach ($fileAttachmentResolverAspectReferences as $fileAttachmentResolverAspectReference) {
            $fileAttachmentResolverAspect = GeneralUtility::makeInstance($fileAttachmentResolverAspectReference);
            if (!$fileAttachmentResolverAspect instanceof FileAttachmentResolverAspectInterface) {
                throw new InvalidHookException('Invalid hook definition for FileAttachmentResolverAspect', 1661774717);
            }
            $result[] = $fileAttachmentResolverAspect;
        }

        return $result;
    }

    /**
     * Extracts from text fields (text/input)
     *
     * @param string $fieldName
     * @param array $record
     * @return int[] uids of valid files
     */
    protected function detectFilesInTextField(string $fieldName, array $record): array
    {
        $fileUids = $this->getFileUidsFromReferencedFilesWithT3Syntax($record[$fieldName]);
        return $this->checkFiles($fileUids);
    }

    /**
     * Retrieves referenced files uid's with the new reference syntax t3://file...
     *
     * @param string $value
     * @return array
     */
    protected function getFileUidsFromReferencedFilesWithT3Syntax($value): array
    {
        $fileUids = [];

        /** @var $fileLinkExtractor FileLinkExtractionService */
        $fileLinkExtractor = GeneralUtility::makeInstance(FileLinkExtractionService::class);

        /** @var $linkService LinkService */
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $fileLinks =  $fileLinkExtractor->extract($value);
        foreach ($fileLinks as $fileLink) {
            $link = $linkService->resolve($fileLink);

            if (!isset($link['file'])) {
                continue;
            }
            /** @var $file File */
            $file = $link['file'];
            $fileUids[] = $file->getUid();
        }

        return $fileUids;
    }

    /**
     * Extracts from Group field
     *
     * @param string $tableName
     * @param string $fieldName
     * @param array $record
     * @param array $fieldConfiguration
     *
     * @return int[]
     */
    protected function detectFilesInGroupField($tableName, $fieldName, array $record, array $fieldConfiguration): array
    {
        $values = GeneralUtility::trimExplode(',', $record[$fieldName]);
        if ($values === []) {
            return [];
        }
        $internalType = $fieldConfiguration['internal_type'];
        $fileUids = [];
        switch ($internalType) {
            case 'db':
                if ($fieldConfiguration['allowed'] === '*'
                    || GeneralUtility::inList($fieldConfiguration['allowed'], 'sys_file')
                    || GeneralUtility::inList($fieldConfiguration['allowed'], 'sys_file_collection')
                ) {
                    if (isset($fieldConfiguration['MM'])) {
                        if ($fieldConfiguration['MM'] === 'sys_file_reference') {
                            $repository = GeneralUtility::makeInstance(FileRepository::class);
                            /** @var FileReference[] $fileReferences */
                            $fileReferences = $repository->findByRelation($tableName, $fieldName, $record['uid']);
                            foreach ($fileReferences as $fileReference) {
                                if (!$this->isValidFileReference($fileReference)) {
                                    continue;
                                }

                                $fileUids[] = $fileReference->getOriginalFile()->getUid();
                            }
                        }
                    } else {
                        foreach ($values as $value) {
                            list($table, $uid) = BackendUtility::splitTable_Uid($value);
                            if ((empty($table) && $fieldConfiguration['allowed'] == 'sys_file') || $table == 'sys_file') {
                                $fileUids[] = (int)$uid;
                            } elseif ((empty($table) && $fieldConfiguration['allowed'] == 'sys_file_collection') || $table == 'sys_file_collection') {
                                $this->addFileUidsFromCollectionToArray($uid, $fileUids);
                            }
                        }
                    }
                }
                break;
            case 'file_reference':
                foreach ($values as $fileReference) {
                    // that's safe since 'file_reference' only works locally with fileadmin/
                    if (file_exists(Environment::getPublicPath() . $fileReference)) {
                        $fileObject = $this->getResourceFactory()->getFileObjectFromCombinedIdentifier($fileReference);
                        $fileUids[] = $fileObject->getUid();
                    }
                }
                break;
            case 'file':
                // solrfal does not support non FAL fields
                break;
            case 'folder':
                foreach ($values as $folderPath) {
                    try {
                        $folderObject = $this->getResourceFactory()->getFolderObjectFromCombinedIdentifier($folderPath);
                        foreach ($folderObject->getFiles() as $fileObject) {
                            $fileUids[] = $fileObject->getUid();
                        }
                    } /* @noinspection PhpRedundantCatchClauseInspection */ catch (FolderDoesNotExistException $e) {
                        continue;
                    }
                }
                break;
            default:
        }
        return $fileUids;
    }

    /**
     * Adds the UIDs of the files found in the collection with the given
     * $collectionUid to the $fileUidArray.
     *
     * @param int $collectionUid The UID of the collection
     * @param array $fileUidArray The array to which the file UIDs will be added.
     */
    protected function addFileUidsFromCollectionToArray($collectionUid, array &$fileUidArray)
    {
        $collectionRepository = GeneralUtility::makeInstance(FileCollectionRepository::class);
        $fileCollection = $collectionRepository->findByUid($collectionUid);

        if ($fileCollection instanceof AbstractFileCollection) {
            $fileCollection->loadContents();
            /** @var File $file */
            foreach ($fileCollection->getItems() as $file) {
                $fileUidArray[] = $file->getUid();
            }
        }
    }

    /**
     * Extracts from inline fields
     *
     * @param string $tableName
     * @param string $fieldName
     * @param array $record
     * @param array $fieldConfiguration
     * @return int[]
     */
    protected function detectFilesInInlineField($tableName, $fieldName, array $record, array $fieldConfiguration): array
    {
        $fileUids = [];

        if ($fieldConfiguration['foreign_table'] === 'sys_file_reference') {
            /* @var FileCollector $fileCollector */
            $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
            $fileCollector->addFilesFromRelation($tableName, $fieldName, $record);
            $fileReferences = $fileCollector->getFiles();
            foreach ($fileReferences as $fileReference) {
                $fileReference = $this->getUpdatedFileReference($fileReference);

                if (!$this->isValidFileReference($fileReference)) {
                    continue;
                }

                $fileUids[] = $fileReference->getOriginalFile()->getUid();
            }
        }

        return $fileUids;
    }

    /**
     * Get updated and uncached file reference
     *
     * We do this since the ResourceFactory caches the
     * file reference objects and we cannot be sure that this
     * object is up-to-date
     *
     * @param FileReference $fileReference
     * @return FileReference $fileReference
     */
    protected function getUpdatedFileReference(FileReference $fileReference): FileReference
    {
        try {
            $fileReferenceData = BackendUtility::getRecord('sys_file_reference', $fileReference->getUid());
            $fileReference = $this->getResourceFactory()
                ->createFileReferenceObject($fileReferenceData);
        } catch (Exception $e) {
            /* @var Logger $logger */
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error($e->getMessage());
        }

        return $fileReference;
    }

    /**
     * Checks if file reference is valid and may be added to the index queue
     *
     * @param FileReference $fileReference
     * @return bool
     */
    protected function isValidFileReference(FileReference $fileReference): bool
    {
        return !($fileReference->getReferenceProperty('hidden') || $fileReference->isMissing());
    }

    /**
     * Checks if files are valid and removes invalid files that may not be added to the index queue
     *
     * @param array $fileUids
     * @return array
     */
    protected function checkFiles(array $fileUids): array
    {
        $checkedFileUids = [];

        foreach ($fileUids as $fileUid) {
            try {
                $file = $this->getResourceFactory()->getFileObject((int)$fileUid);
                if (
                    !$file->isMissing()
                    && !$file->isDeleted()
                    && $file->exists()
                ) {
                    $checkedFileUids[] = $file->getUid();
                }
            } catch (Exception $e) {
                /* @var Logger $logger */
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->error('File not found: ' . $fileUid);
            }
        }

        return $checkedFileUids;
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
