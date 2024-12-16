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

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\FrontendEnvironment;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StorageContext
 */
class RecordContext extends AbstractContext
{
    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string
     */
    protected string $field;

    /**
     * @var int
     */
    protected int $uid;

    /**
     * RecordContext constructor.
     * @param Site $site
     * @param Rootline $accessRestrictions
     * @param string $table
     * @param string $field
     * @param int $uid
     * @param string $indexingConfiguration
     * @param int $language
     */
    public function __construct(
        Site $site,
        Rootline $accessRestrictions,
        string $table,
        string $field,
        int $uid,
        string $indexingConfiguration,
        int $language = 0
    ) {
        parent::__construct($site, $accessRestrictions, $indexingConfiguration, $language);
        $this->table = $table;
        $this->field = $field;
        $this->uid = $uid;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'context_record_table'                  => $this->getTable(),
                'context_record_uid'                    => $this->getUid(),
                'context_record_field'                  => $this->getField(),
                'context_record_indexing_configuration' => $this->getIndexingConfiguration(),
            ]
        );
    }

    /**
     * @return string
     */
    public function getContextIdentifier(): string
    {
        return 'record';
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * Returns an array of context specific field to add to the solr document
     *
     * @return array
     */
    public function getAdditionalStaticDocumentFields(): array
    {
        return array_merge(
            parent::getAdditionalStaticDocumentFields(),
            [
                'fileReferenceType' => $this->getTable(),
                'fileReferenceUid' => $this->getUid(),
            ]
        );
    }

    /**
     * Resolves the field-processing TypoScript configuration which is specific
     * to the current context.
     * Will be merged in the default field-processing configuration and takes
     * precedence over the default configuration.
     *
     * @return array
     * @throws DBALDriverException
     */
    public function getSpecificFieldConfigurationTypoScript(): array
    {
        $fieldConfiguration = parent::getSpecificFieldConfigurationTypoScript();
        return $this->injectReferenceInformation($fieldConfiguration);
    }

    /**
     * Injects configuration so FileReferenceTitle and FileReferenceUrl
     * are set from related record.
     *
     * @param array $fieldConfiguration
     *
     * @return array
     * @throws DBALDriverException
     */
    protected function injectReferenceInformation(array $fieldConfiguration = []): array
    {
        // if there are no reference title / url configured manually, try to copy configuration form record index-queue
        if (!isset($fieldConfiguration['__RecordContext']) && !is_array($fieldConfiguration['__RecordContext.'] ?? null)) {
            /* @var TypoScriptConfiguration $indexingConfigurations */
            $indexingConfigurations = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
                $this->getSite()->getRootPageId(),
                $this->getLanguage()
            );
            $fieldConfigurationOfRecord = $indexingConfigurations->getObjectByPathOrDefault('plugin.tx_solr.index.queue.' . $this->getIndexingConfiguration() . '.fields.', []);
            $fieldConfiguration['__RecordContext'] = '_';
            $fieldConfiguration['__RecordContext.'] = [];

            if (isset($fieldConfigurationOfRecord['title'])) {
                $fieldConfiguration['__RecordContext.']['fileReferenceTitle'] = $fieldConfigurationOfRecord['title'];
            }
            if (isset($fieldConfigurationOfRecord['title.']) && is_array($fieldConfigurationOfRecord['title.'])) {
                $fieldConfiguration['__RecordContext.']['fileReferenceTitle.'] = $fieldConfigurationOfRecord['title.'];
            }
            if (isset($fieldConfigurationOfRecord['url'])) {
                $fieldConfiguration['__RecordContext.']['fileReferenceUrl'] = $fieldConfigurationOfRecord['url'];
            }
            if (isset($fieldConfigurationOfRecord['url.']) && is_array($fieldConfigurationOfRecord['url.'])) {
                $fieldConfiguration['__RecordContext.']['fileReferenceUrl.'] = $fieldConfigurationOfRecord['url.'];
            }
        }
        return $fieldConfiguration;
    }

    /**
     * Returns an identifier, which will be used for looking up special
     * configurations in TypoScript like storage uid in storageContext
     * or table name in recordContext
     *
     * @return string
     */
    protected function getIdentifierForItemSpecificFieldConfiguration(): string
    {
        return $this->getTable();
    }
}
