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
use function json_encode;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Context
 */
abstract class AbstractContext implements ContextInterface
{
    /**
     * @var Site
     */
    protected Site $site;

    /**
     * @var Rootline
     */
    protected Rootline $accessRestrictions;

    /**
     * @var int
     */
    protected int $language = 0;

    /**
     * @var array
     */
    protected array $additionalDocumentFields = [];

    /**
     * @var string
     */
    protected string $indexingConfiguration = '';

    /**
     * @var bool
     */
    protected bool $error = false;

    /**
     * @var string
     */
    protected string $errorMessage = '';

    /**
     * @param Site $site
     * @param Rootline $accessRestrictions
     * @param string $indexingConfiguration
     * @param int $language
     */
    public function __construct(
        Site $site,
        Rootline $accessRestrictions,
        string $indexingConfiguration = '',
        int $language = 0
    ) {
        $this->site               = $site;
        $this->accessRestrictions = $accessRestrictions;
        $this->language           = $language;
        $this->indexingConfiguration = $indexingConfiguration;
    }

    /**
     * @return string
     */
    public function getIndexingConfiguration(): string
    {
        return $this->indexingConfiguration;
    }

    /**
     * @return bool
     */
    public function getError(): bool
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return Rootline
     */
    public function getAccessRestrictions(): Rootline
    {
        return $this->accessRestrictions;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @return Site
     */
    public function getSite(): Site
    {
        return $this->site;
    }

    /**
     * Returns the array representation for database storage
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'context_type'                => $this->getContextIdentifier(),
            'context_language'            => $this->getLanguage(),
            'context_access_restrictions' => $this->getAccessRestrictions()->__toString(),
            'context_site'                => $this->getSite()->getRootPageId(),
            'context_additional_fields'   => json_encode($this->additionalDocumentFields),
            'context_record_indexing_configuration' => $this->getIndexingConfiguration(),
            'error'                       => (int)$this->getError(),
            'error_message'               => $this->getErrorMessage(),
        ];
    }

    /**
     * Returns the pageId of this context
     *
     * @return int
     */
    public function getPageId(): int
    {
        return $this->getSite()->getRootPageId();
    }

    /**
     * @param array $additionalDocumentFields
     */
    public function setAdditionalDocumentFields(array $additionalDocumentFields)
    {
        $this->additionalDocumentFields = $additionalDocumentFields;
    }

    /**
     * @return array
     */
    public function getAdditionalStaticDocumentFields(): array
    {
        return $this->additionalDocumentFields;
    }

    /**
     * Returns an array of context specific field to add to the solr document,
     * dynamically calculated from the FILE
     *
     * @param File $file
     *
     * @return array
     */
    public function getAdditionalDynamicDocumentFields(File $file): array
    {
        return [];
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
        /* @var TypoScriptConfiguration $fileConfiguration */
        $fileConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
            $this->getSite()->getRootPageId(),
            $this->getLanguage()
        );

        $contextConfiguration = $fileConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.queue._FILES.' . $this->getContextIdentifier() . 'Context.', []);
        $configurationArray = [];

        if (array_key_exists('default.', $contextConfiguration) && is_array($contextConfiguration['default.'])) {
            $configurationArray = $contextConfiguration['default.'];
        }

        $itemSpecificConfigKey = $this->getIdentifierForItemSpecificFieldConfiguration() . '.';
        if (array_key_exists($itemSpecificConfigKey, $contextConfiguration) && is_array($contextConfiguration[$itemSpecificConfigKey])) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $configurationArray,
                $contextConfiguration[$itemSpecificConfigKey]
            );
        }
        return $configurationArray;
    }

    /**
     * Returns an identifier, which will be used for looking up special
     * configurations in TypoScript like storage uid in storageContext
     * or table name in recordContext
     *
     * @return string
     */
    abstract protected function getIdentifierForItemSpecificFieldConfiguration(): string;
}
