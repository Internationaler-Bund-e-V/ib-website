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

namespace ApacheSolrForTypo3\Solrfal\Context;

/**
 * ContextFactoryInterface
 */
interface ContextFactoryInterface
{
    /**
     * Factory Method to create a Context based on an entry in tx_solr_indexqueue_file
     *
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
     */
    public function getByRecord(array $row): ContextInterface;
}
