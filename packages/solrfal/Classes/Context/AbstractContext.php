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
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Context\Context as TYPO3CoreContext;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function json_encode;

/**
 * Class AbstractContext
 */
abstract class AbstractContext implements ContextInterface
{
    /**
     * @var array<string, string|int>
     */
    protected array $additionalDocumentFields = [];

    protected bool $error = false;

    protected string $errorMessage = '';

    public function __construct(
        protected readonly Site $site,
        protected readonly Rootline $accessRestrictions,
        protected readonly int $uid,
        protected readonly int $pid,
        protected readonly string $indexingConfiguration = '',
        protected readonly int $language = 0,
    ) {}

    public function getIndexingConfiguration(): string
    {
        return $this->indexingConfiguration;
    }

    public function getError(): bool
    {
        return $this->error;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getAccessRestrictions(): Rootline
    {
        return $this->accessRestrictions;
    }

    public function getLanguage(): int
    {
        return $this->language;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    /**
     * @inheritDoc
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
            'context_record_uid'                    => $this->getUid(),
            'context_record_pid'                    => $this->getPid(),
            'error'                       => (int)$this->getError(),
            'error_message'               => $this->getErrorMessage(),
        ];
    }

    /**
     * @inheritDoc
     */
    final public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @inheritDoc
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @inheritDoc
     */
    public function getPidForCoreContext(): int
    {
        return $this->getSite()->getRootPageId();
    }

    /**
     * @param array<string, string|int> $additionalDocumentFields
     */
    public function setAdditionalDocumentFields(array $additionalDocumentFields): void
    {
        $this->additionalDocumentFields = $additionalDocumentFields;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalStaticDocumentFields(): array
    {
        return $this->additionalDocumentFields;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalDynamicDocumentFields(File $file): array
    {
        return [];
    }

    /**
     * @inheritDoc
     *
     * @throws DBALException
     */
    public function getSpecificFieldConfigurationTypoScript(): array
    {
        /** @var TypoScriptConfiguration $fileConfiguration */
        $fileConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
            $this->getSite()->getRootPageId(),
            $this->getLanguage()
        );

        $contextConfiguration = $fileConfiguration->getObjectByPathOrDefault(
            'plugin.tx_solr.index.queue._FILES.' . $this->getContextIdentifier() . 'Context.'
        );
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

    protected function getTYPO3CoreContext(): TYPO3CoreContext
    {
        return GeneralUtility::makeInstance(TYPO3CoreContext::class);
    }

    /**
     * Returns an identifier, which will be used for looking up special
     * configurations in TypoScript like storage uid in storageContext
     * or table name in recordContext
     */
    abstract protected function getIdentifierForItemSpecificFieldConfiguration(): string;
}
