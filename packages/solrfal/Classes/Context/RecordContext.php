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
use Doctrine\DBAL\Exception as BDALException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StorageContext
 */
class RecordContext extends AbstractContext
{
    public function __construct(
        Site $site,
        Rootline $accessRestrictions,
        protected readonly string $table,
        protected readonly string $field,
        int $uid,
        int $pid,
        string $indexingConfiguration,
        int $language = 0,
    ) {
        parent::__construct(
            $site,
            $accessRestrictions,
            $uid,
            $pid,
            $indexingConfiguration,
            $language,
        );
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'context_record_table'                  => $this->getTable(),
                'context_record_field'                  => $this->getField(),
                'context_record_indexing_configuration' => $this->getIndexingConfiguration(),
            ]
        );
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getPidForCoreContext(): int
    {
        return $this->getPid();
    }

    public function getContextIdentifier(): string
    {
        return 'record';
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @inheritDoc
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
     * @throws BDALException
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
     * @param array<string, string|int|array<string, mixed>> $fieldConfiguration
     * @return array<string, string|int|array<string, mixed>>
     *
     * @throws BDALException
     */
    protected function injectReferenceInformation(array $fieldConfiguration = []): array
    {
        // if there are no reference title / url configured manually, try to copy configuration form record index-queue
        if (!isset($fieldConfiguration['__RecordContext']) && !is_array($fieldConfiguration['__RecordContext.'] ?? null)) {
            /** @var TypoScriptConfiguration $indexingConfigurations */
            $indexingConfigurations = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
                $this->getSite()->getRootPageId(),
                $this->getLanguage()
            );
            $fieldConfigurationOfRecord = $indexingConfigurations->getObjectByPathOrDefault('plugin.tx_solr.index.queue.' . $this->getIndexingConfiguration() . '.fields.');
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
     */
    protected function getIdentifierForItemSpecificFieldConfiguration(): string
    {
        return $this->getTable();
    }
}
