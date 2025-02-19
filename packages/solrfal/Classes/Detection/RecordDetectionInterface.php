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

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;

/**
 * Interface RecordDetectionInterface
 */
interface RecordDetectionInterface
{
    public function __construct(Site $site, TypoScriptConfiguration $siteConfiguration = null);

    /**
     * Initializes the file index queue
     */
    public function initializeQueue(
        string $indexingConfigurationName,
        ?bool $indexQueueForConfigurationNameIsInitialized = false
    ): bool;

    public function recordCreated(string $table, int $uid): void;

    public function recordUpdated(string $table, int $uid): void;

    public function recordDeleted(string $table, int $uid): void;

    /**
     * Handles new sys_file entries
     */
    public function fileIndexRecordCreated(string $table, int $uid): void;

    /**
     * Handles updates on sys_file entries
     */
    public function fileIndexRecordUpdated(string $table, int $uid): void;

    /**
     * Handles deletions of sys_file entries
     */
    public function fileIndexRecordDeleted(string $table, int $uid): void;
}
