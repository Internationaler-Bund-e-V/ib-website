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

interface FileAttachmentResolverAspectInterface
{
    /**
     * This method should be implemented by to hooks to modify the fileUids.
     *
     * @param int[] $fileUids the list of file UIDs
     * @param string $tableName The table name to extract file references from.
     * @param string $fieldName The field name to extract file references from.
     * @param array<string, int|string|bool|null> $record
     *
     * @return int[] the list of valid file UIDs
     */
    public function postDetectFilesInField(
        array $fileUids,
        string $tableName,
        string $fieldName,
        array $record,
        FileAttachmentResolver $fileAttachmentResolver,
    ): array;
}
