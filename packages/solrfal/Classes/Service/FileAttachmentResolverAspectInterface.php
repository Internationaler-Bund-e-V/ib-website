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
     * @param array $fileUids
     * @param string $tableName
     * @param string $fieldName
     * @param array $record
     * @param FileAttachmentResolver $fileAttachmentResolver
     * @return array
     */
    public function postDetectFilesInField(
        array $fileUids,
        string $tableName,
        string $fieldName,
        array $record,
        FileAttachmentResolver $fileAttachmentResolver
    ): array;
}
