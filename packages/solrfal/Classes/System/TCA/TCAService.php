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

namespace ApacheSolrForTypo3\Solrfal\System\TCA;

use ApacheSolrForTypo3\Solr\System\TCA\TCAService as SolrTCAService;

/**
 * Class to encapsulate TCA specific logic
 */
class TCAService extends SolrTCAService
{
    /**
     * Retrieves the record's language uid
     *
     * @param string $table The table name to fetch the language UID from.
     * @param array<string, int|string|bool|null> $record The record to fetch the language UID from.
     * @return int The real language UID of given record
     */
    public function getRecordLanguageUid(string $table, array $record): int
    {
        if (empty($this->tca[$table]['ctrl']['languageField'] ?? '')) {
            return 0;
        }

        return (int)($record[$this->tca[$table]['ctrl']['languageField']] ?? 0);
    }
}
