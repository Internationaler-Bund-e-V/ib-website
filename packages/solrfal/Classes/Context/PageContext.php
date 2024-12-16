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
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Exception\Exception;
use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StorageContext
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class PageContext extends RecordContext
{
    /**
     * @var int
     */
    protected int $pageId;

    /**
     * @return string
     */
    public function getContextIdentifier(): string
    {
        return 'page';
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @param Site $site
     * @param Rootline $accessRestrictions
     * @param int $pageUid
     * @param string|null $table
     * @param string|null $field
     * @param int|null $uid
     * @param int|null $language
     */
    public function __construct(
        Site $site,
        Rootline $accessRestrictions,
        int $pageUid,
        string $table,
        string $field,
        int $uid,
        int $language = 0
    ) {
        parent::__construct($site, $accessRestrictions, $table, $field, $uid, 'pages', $language);
        $this->pageId = $pageUid;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'context_record_page' => $this->getPageId(),
            ]
        );
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
                'fileReferenceUrl' => $this->getPageId(),
            ]
        );
    }

    /**
     * @param File $file
     *
     * @return array
     * @throws DBALDriverException
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getAdditionalDynamicDocumentFields(File $file): array
    {
        $dynamicFields = [];

        $pageRepository = $this->getPageRepository();
        $pageRow = $pageRepository->getPage($this->getPageId(), true);

        if (!empty($pageRow)) {
            $dynamicFields['fileReferenceTitle'] = $pageRow['title'];

            /* @var TypoScriptConfiguration $pageContextConfiguration */
            $pageContextConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
                $this->getSite()->getRootPageId(),
                $this->getLanguage()
            );

            /** @var Rootline $accessRootline */
            $accessRootline = GeneralUtility::makeInstance(Rootline::class);
            $enableFields = $pageContextConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.pageContext.enableFields.', []);
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
                            $rootlineElement = GeneralUtility::makeInstance(RootlineElement::class, trim($this->getPageId() . ':' . $pageRow[$fieldName]));
                            $accessRootline->push($rootlineElement);
                        }
                        break;
                    default:
                }
            }

            // content element access data
            $contentAccessGroupField = $pageContextConfiguration->getValueByPathOrDefaultValue('plugin.tx_solr.index.enableFileIndexing.pageContext.contentEnableFields.accessGroups', '');
            if ($contentAccessGroupField !== '' && $this->getUid() > 0) {
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
        return $fileConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.queue._FILES.' . $this->getContextIdentifier() . 'Context.', []);
    }

    /**
     * Returns the page repository
     *
     * @return PageRepository
     * @throws DBALDriverException
     * @throws Exception
     * @throws SiteNotFoundException
     */
    protected function getPageRepository(): PageRepository
    {
        $tsfe = GeneralUtility::makeInstance(Tsfe::class)
            ->getTsfeByPageIdAndLanguageId($this->getPageId(), $this->getLanguage());
        // @todo: The TSFE is null, if the access restricted page is requested.
        //        So the initialization must happen after FE-Groups authorization, like it is done on page indexing.
        if (null === $tsfe) {
            $coreContext = GeneralUtility::makeInstance(Context::class);
            $coreContext->setAspect('language', new LanguageAspect($this->language));
        } else {
            $coreContext = $tsfe->getContext();
        }
        return GeneralUtility::makeInstance(PageRepository::class, $coreContext);
    }
}
