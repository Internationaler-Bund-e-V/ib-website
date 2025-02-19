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

namespace ApacheSolrForTypo3\Solrfal\System\Configuration;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class encapsulates the access to the extension configuration.
 */
class ExtensionConfiguration
{
    /**
     * Extension Configuration
     * @var array<string, mixed>
     */
    protected array $extensionConfiguration;

    /**
     * ExtensionConfiguration constructor.
     *
     * @param array<string, mixed> $extensionConfiguration
     */
    public function __construct(
        array $extensionConfiguration
    ) {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * This method is used to check if a record of this table should only use the detectors of the current site
     * and not of all sites in the system.
     */
    public function getIsSiteExclusiveRecordTable(string $tableNameToCheck): bool
    {
        $siteExclusiveTablesList = trim($this->getConfigurationOrDefaultValue('siteExclusiveRecordTables', 'pages, tt_content, sys_file_reference'));
        if (empty($siteExclusiveTablesList)) {
            return false;
        }

        $siteExclusiveTables = GeneralUtility::trimExplode(',', $siteExclusiveTablesList);

        return in_array($tableNameToCheck, $siteExclusiveTables);
    }

    protected function getConfigurationOrDefaultValue(string $key, mixed $defaultValue): mixed
    {
        return $this->extensionConfiguration[$key] ?? $defaultValue;
    }
}
