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
use ApacheSolrForTypo3\Solr\Access\RootlineElement;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\FrontendEnvironment;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Exception\Exception as ExtSolrFrontendEnvironmentException;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StorageContext
 */
class PageContext extends RecordContext
{
    public function getContextIdentifier(): string
    {
        return 'page';
    }

    public function __construct(
        Site $site,
        Rootline $accessRestrictions,
        string $table,
        string $field,
        int $uid,
        int $pid,
        int $language = 0,
    ) {
        parent::__construct(
            $site,
            $accessRestrictions,
            $table,
            $field,
            $uid,
            $pid,
            'pages',
            $language,
        );
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalStaticDocumentFields(): array
    {
        return array_merge(
            parent::getAdditionalStaticDocumentFields(),
            [
                'fileReferenceUrl' => $this->getPidForCoreContext(),
            ]
        );
    }

    public function getPidForCoreContext(): int
    {
        if ($this->getTable() === 'pages') {
            return $this->getUid();
        }
        if ($this->getTable() === 'tt_content') {
            return $this->getPid();
        }
        return $this->site->getRootPageId();
    }

    /**
     * @throws SiteNotFoundException
     * @throws ExtSolrFrontendEnvironmentException
     * @throws DBALException
     */
    public function getAdditionalDynamicDocumentFields(File $file): array
    {
        $dynamicFields = [];

        $pageRepository = $this->getPageRepository();
        $pageRow = $pageRepository->getPage($this->getPidForCoreContext(), true);

        if (!empty($pageRow)) {
            $dynamicFields['fileReferenceTitle'] = $pageRow['title'];

            /** @var TypoScriptConfiguration $pageContextConfiguration */
            $pageContextConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
                $this->getSite()->getRootPageId(), // @todo: Check if $this->getPidForCoreContext() is better on this place. Note the mount-point stuff.
                $this->getLanguage()
            );

            /** @var Rootline $accessRootline */
            $accessRootline = GeneralUtility::makeInstance(Rootline::class);
            $enableFields = $pageContextConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.enableFields.');
            foreach ($enableFields as $identifier => $fieldName) {
                switch ($identifier) {
                    case 'endtime':
                        if ((int)$pageRow[$fieldName] !== 0) {
                            $dynamicFields['endtime'] = (int)$pageRow[$fieldName];
                        }
                        break;
                    case 'accessGroups':
                        if (trim($pageRow[$fieldName])) {
                            /** @var RootlineElement $rootlineElement */
                            $rootlineElement = GeneralUtility::makeInstance(RootlineElement::class, trim($this->getPidForCoreContext() . ':' . $pageRow[$fieldName]));
                            $accessRootline->push($rootlineElement);
                        }
                        break;
                    default:
                }
            }

            // content element access data
            $contentAccessGroupField = $pageContextConfiguration->getValueByPathOrDefaultValue('plugin.tx_solr.index.enableFileIndexing.pageContext.contentEnableFields.accessGroups', '');
            if ($contentAccessGroupField !== '' && $this->getPidForCoreContext() > 0) {
                $contentElement = BackendUtility::getRecord($this->getTable(), $this->getUid());
                /** @var RootlineElement $rootlineElement */
                $rootlineElement = GeneralUtility::makeInstance(RootlineElement::class, trim($contentElement[$contentAccessGroupField] ?? ''));
            } else {
                $rootlineElement = GeneralUtility::makeInstance(RootlineElement::class, '');
            }
            $accessRootline->push($rootlineElement);

            // set access data
            $dynamicFields['access'] = $accessRootline->__toString();
        }

        return $dynamicFields;
    }

    /**
     * Resolves the field-processing TypoScript configuration which is specific
     * to the current context.
     * Will be merged in the default field-processing configuration and takes
     * precedence over the default configuration.
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
        return $fileConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.queue._FILES.' . $this->getContextIdentifier() . 'Context.');
    }

    /**
     * Returns the page repository
     *
     * @throws DBALException
     * @throws ExtSolrFrontendEnvironmentException
     * @throws SiteNotFoundException
     */
    protected function getPageRepository(): PageRepository
    {
        $tsfe = GeneralUtility::makeInstance(Tsfe::class)
            ->getTsfeByPageIdAndLanguageId($this->getPidForCoreContext(), $this->getLanguage());
        // @todo: The TSFE is null, if the access restricted page is requested.
        //        So the initialization must happen after FE-Groups authorization, like it is done on page indexing.
        if ($tsfe === null) {
            /** @var Context $coreContext */
            $coreContext = clone GeneralUtility::makeInstance(Context::class);
            $coreContext->setAspect('language', new LanguageAspect($this->language));
        } else {
            $coreContext = $tsfe->getContext();
        }
        return GeneralUtility::makeInstance(PageRepository::class, $coreContext);
    }
}
