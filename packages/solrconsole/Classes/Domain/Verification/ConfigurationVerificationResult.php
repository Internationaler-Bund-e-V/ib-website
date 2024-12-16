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

namespace ApacheSolrForTypo3\Solrconsole\Domain\Verification;

class ConfigurationVerificationResult
{

    /**
     * @var int
     */
    protected $languageUid = 0;

    /**
     * @var string
     */
    protected $configurationName = '';

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var array
     */
    protected $typo3Uids = [];

    /**
     * @var array
     */
    protected $solrUids  = [];

    /**
     * @var int
     */
    protected $indexQueueErrors = 0;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @return array
     */
    public function getMissingInSolr(): array
    {
        return array_diff($this->typo3Uids, $this->solrUids);
    }

    /**
     * @return array
     */
    public function getMissingInTYPO3(): array
    {
        return array_diff($this->solrUids, $this->typo3Uids);
    }

    /**
     * @return array
     */
    public function getTypo3Uids(): array
    {
        return $this->typo3Uids;
    }

    /**
     * @param array $typo3Uids
     */
    public function setTypo3Uids(array $typo3Uids)
    {
        $this->typo3Uids = $typo3Uids;
    }

    /**
     * @return array
     */
    public function getSolrUids(): array
    {
        return $this->solrUids;
    }

    /**
     * Sets the solr uids.
     * Note: The variants of Solr Document IDs for FE-User access restriction must be removed,
     *       since the Index-Queue-Item and record state does not provide information about the count of access variants.
     *
     * @param array $solrUids
     */
    public function setSolrUids(array $solrUids)
    {
        $this->solrUids = array_unique($solrUids);
    }

    /**
     * @return int
     */
    public function getLanguageUid(): int
    {
        return $this->languageUid;
    }

    /**
     * @param int $languageUid
     */
    public function setLanguageUid(int $languageUid)
    {
        $this->languageUid = $languageUid;
    }

    /**
     * @return string
     */
    public function getConfigurationName(): string
    {
        return $this->configurationName;
    }

    /**
     * @param string $configurationName
     */
    public function setConfigurationName(string $configurationName)
    {
        $this->configurationName = $configurationName;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    public function addError($errorMessage)
    {
        $this->errors[] = $errorMessage;
    }

    /**
     * @return int
     */
    public function getIndexQueueErrors(): int
    {
        return $this->indexQueueErrors;
    }

    /**
     * @param int $indexQueueErrors
     */
    public function setIndexQueueErrors(int $indexQueueErrors)
    {
        $this->indexQueueErrors = $indexQueueErrors;
    }
}