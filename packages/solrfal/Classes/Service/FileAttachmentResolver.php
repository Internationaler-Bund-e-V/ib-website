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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileCollectionRepository;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Resource\FileCollector;

/**
 * Class FileAttachmentResolver
 */
class FileAttachmentResolver implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Detects attachments of a single field
     *
     * @param string $tableName The table name to extract file references from.
     * @param string $fieldName The field name to extract file references from.
     * @param array{uid: int, pid: int}|array<string, int|string|bool|null> $record
     *
     * @return int[] uids of valid files
     *
     * @throws InvalidHookException
     * @throws ResourceDoesNotExistException
     * @throws UnknownLinkHandlerException
     */
    public function detectFilesInField(
        string $tableName,
        string $fieldName,
        array $record,
    ): array {
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
            case 'file':
                $fileUids = $this->detectFilesInInlineField($tableName, $fieldName, $record, $fieldConfiguration);
                break;
            case 'flex':
                // todo: files in flexforms are not supported yet
                break;
            default:
                break;
        }

        return $this->applyPostDetectFilesInFieldHook($fileUids, $tableName, $fieldName, $record);
    }

    /**
     * Calls postDetectFilesInField on the configured FileAttachmentResolverAspects:
     *
     * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect']
     *
     * @param int[] $fileUids
     * @param string $tableName
     * @param string $fieldName
     * @param array<string, int|string|bool|null> $record
     *
     * @return int[] uids of files
     *
     * @throws InvalidHookException
     */
    protected function applyPostDetectFilesInFieldHook(
        array $fileUids,
        string $tableName,
        string $fieldName,
        array $record,
    ): array {
        $fileAttachmentResolverAspects = $this->getFileAttachmentResolverAspects();
        if (count($fileAttachmentResolverAspects) === 0) {
            return $fileUids;
        }

        // we have valid hooks and trigger them
        foreach ($fileAttachmentResolverAspects as $fileAttachmentResolverAspect) {
            $fileUids = $fileAttachmentResolverAspect->postDetectFilesInField($fileUids, $tableName, $fieldName, $record, $this);
        }

        // check files before return them
        return $this->checkFiles($fileUids);
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
        $result = [];
        $fileAttachmentResolverAspectReferences = (array)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect'] ?? []);

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
     * @param string $fieldName The field name to extract file references from.
     * @param array<string, int|string|bool|null> $record The record to extract the file references from
     *
     * @return int[] uids of valid files
     *
     * @throws UnknownLinkHandlerException
     */
    protected function detectFilesInTextField(string $fieldName, array $record): array
    {
        $fileUids = $this->getFileUidsFromReferencedFilesWithT3Syntax((string)$record[$fieldName]);
        return $this->checkFiles($fileUids);
    }

    /**
     * Retrieves referenced files uids with the new reference syntax t3://file...
     *
     * @return int[] uids of valid files
     *
     * @throws UnknownLinkHandlerException
     */
    protected function getFileUidsFromReferencedFilesWithT3Syntax(
        string $value,
    ): array {
        $fileUids = [];

        /** @var FileLinkExtractionService $fileLinkExtractor */
        $fileLinkExtractor = GeneralUtility::makeInstance(FileLinkExtractionService::class);

        /** @var LinkService $linkService */
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $fileLinks =  $fileLinkExtractor->extract($value);
        foreach ($fileLinks as $fileLink) {
            $link = $linkService->resolve($fileLink);

            if (!isset($link['file'])) {
                continue;
            }
            /** @var File $file */
            $file = $link['file'];
            $fileUids[] = $file->getUid();
        }

        return $fileUids;
    }

    /**
     * Extracts from Group field
     *
     * @param string $tableName The table name to extract file references from.
     * @param string $fieldName The field name to extract file references from.
     * @param array{uid: int, pid: int}|array<string, int|string|bool|null> $record
     * @param array<string, mixed> $fieldConfiguration
     *
     * @return int[] uids of valid files
     *
     * @throws ResourceDoesNotExistException
     */
    protected function detectFilesInGroupField(
        string $tableName,
        string $fieldName,
        array $record,
        array $fieldConfiguration,
    ): array {
        $values = GeneralUtility::trimExplode(',', (string)$record[$fieldName]);
        if ($values === []) {
            return [];
        }
        $internalType = $fieldConfiguration['internal_type'] ?? (
            // See: https://forge.typo3.org/issues/95384 or https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.5/Important-95384-TCAInternal_typedbOptionalForTypegroup.html
            // * > Since db is the most common use case, TYPO3 now uses this as default. Extension authors can therefore remove the internal_type=db option from TCA type group fields.
            // * https://docs.typo3.org/m/typo3/reference-tca/11.5/en-us/ColumnsConfig/Type/Group/Properties/InternalType.html#columns-group-properties-internal-type
            ($fieldConfiguration['type'] ?? '') === 'group' ? 'db' : ''
        );
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
                            $fileReferences = $repository->findByRelation($tableName, $fieldName, (int)$record['uid']);
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
                                $this->addFileUidsFromCollectionToArray((int)$uid, $fileUids);
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
                    } /* @noinspection PhpRedundantCatchClauseInspection */ catch (FolderDoesNotExistException) {
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
     * @param int[] $fileUidArray The array to which the file UIDs will be added.
     *
     * @throws ResourceDoesNotExistException
     */
    protected function addFileUidsFromCollectionToArray(
        int $collectionUid,
        array &$fileUidArray,
    ): void {
        $collectionRepository = GeneralUtility::makeInstance(FileCollectionRepository::class);
        $fileCollection = $collectionRepository->findByUid($collectionUid);

        if ($fileCollection instanceof AbstractFileCollection) {
            $fileCollection->loadContents();
            /** @var FileReference|ProcessedFile|AbstractFile $file */
            foreach ($fileCollection->getItems() as $file) {
                if (
                    $file instanceof FileReference
                    || $file instanceof ProcessedFile
                    || method_exists($file, 'getOriginalFile')
                ) {
                    $fileUidArray[] = $file->getOriginalFile()->getUid();
                } else {
                    $fileUidArray[] = $file->getUid();
                }
            }
        }
    }

    /**
     * Extracts from inline fields
     *
     * @param string $tableName The table name to extract file references from.
     * @param string $fieldName The field name to extract file references from.
     * @param array<string, int|string|bool|null> $record
     * @param array<string, mixed> $fieldConfiguration
     *
     * @return int[] uids of valid files
     */
    protected function detectFilesInInlineField(
        string $tableName,
        string $fieldName,
        array $record,
        array $fieldConfiguration,
    ): array {
        $fileUids = [];

        if ($fieldConfiguration['foreign_table'] === 'sys_file_reference') {
            /** @var FileCollector $fileCollector */
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
     * We do this since the ResourceFactory caches the file reference objects,
     * and we cannot be sure that this object is up-to-date
     */
    protected function getUpdatedFileReference(FileReference $fileReference): FileReference
    {
        try {
            $fileReferenceData = BackendUtility::getRecord('sys_file_reference', $fileReference->getUid());
            $fileReference = $this->getResourceFactory()
                ->createFileReferenceObject($fileReferenceData);
        } catch (Throwable $e) {
            $this->logger->error(
                'Code: ' . $e->getCode() . PHP_EOL . 'message: ' . $e->getMessage()
            );
        }
        return $fileReference;
    }

    /**
     * Checks if file reference is valid and may be added to the index queue
     */
    protected function isValidFileReference(FileReference $fileReference): bool
    {
        return !($fileReference->getReferenceProperty('hidden') || $fileReference->isMissing());
    }

    /**
     * Checks if files are valid and removes invalid files that may not be added to the index queue
     *
     * @param int[] $fileUids
     * @return int[] uids of valid files
     */
    protected function checkFiles(array $fileUids): array
    {
        $checkedFileUids = [];

        foreach ($fileUids as $fileUid) {
            try {
                $file = $this->getResourceFactory()->getFileObject($fileUid);
                if (
                    !$file->isMissing()
                    && !$file->isDeleted()
                    && $file->exists()
                ) {
                    $checkedFileUids[] = $file->getUid();
                }
            } catch (Throwable $e) {
                $this->logger->error(
                    'File not found: ' . $fileUid . ' due of ERROR code: ' . $e->getCode() . PHP_EOL . 'message: ' . $e->getMessage()
                );
            }
        }

        return $checkedFileUids;
    }

    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}
