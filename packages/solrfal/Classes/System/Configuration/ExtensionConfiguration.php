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
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class ExtensionConfiguration
{
    /**
     * Extension Configuration
     *
     * @var array
     */
    protected array $extensionConfiguration;

    /**
     * ExtensionConfiguration constructor.
     * @param array $extensionConfiguration
     */
    public function __construct(
        array $extensionConfiguration
    ) {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    /**
     * This method is used to check if a record of this table should only use the detectors of the current site
     * and not of all sites in the system.
     *
     * @param string $tableNameToCheck
     * @return bool
     */
    public function getIsSiteExclusiveRecordTable(string $tableNameToCheck): bool
    {
        $siteExclusiveTablesList = trim($this->getConfigurationOrDefaultValue('siteExclusiveRecordTables', 'pages, pages_language_overlay, tt_content, sys_file_reference'));
        if (empty($siteExclusiveTablesList)) {
            return false;
        }

        $siteExclusiveTables = GeneralUtility::trimExplode(',', $siteExclusiveTablesList);

        return in_array($tableNameToCheck, $siteExclusiveTables);
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function getConfigurationOrDefaultValue(string $key, $defaultValue)
    {
        return $this->extensionConfiguration[$key] ?? $defaultValue;
    }
}
