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

namespace ApacheSolrForTypo3\Solrfal\Detection;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\PageDocumentPostProcessor;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Exception;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class PageContextDetectorFrontendIndexingAspect
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class PageContextDetectorFrontendIndexingAspect implements ContentObjectPostInitHookInterface, PageDocumentPostProcessor
{
    /**
     * Uid of files linked in processed content elements
     *
     * @var array
     */
    public static array $collectedFileUids = [];

    /**
     * Content elements processed
     *
     * @var array
     */
    public static array $collectedContentElements = [];

    /**
     * Registers file uids
     * Slot to ResourceStorage::preGetPublicUrl
     *
     * @param ResourceInterface $resourceObject
     */
    public static function registerGeneratedPublicUrl(ResourceInterface $resourceObject): void
    {
        if ($resourceObject instanceof File) {
            static::$collectedFileUids[] = $resourceObject->getUid();
        } elseif ($resourceObject instanceof ProcessedFile) {
            static::$collectedFileUids[] = $resourceObject->getOriginalFile()->getUid();
        } elseif ($resourceObject instanceof FileReference) {
            static::$collectedFileUids[] = $resourceObject->getOriginalFile()->getUid();
        }

        /* @var Logger $logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $logger->info('getPublicUrl called for file: ' . $resourceObject->getCombinedIdentifier());
    }

    /**
     * Allows Modification of the PageDocument
     * Can be used to trigger actions when all contextual variables of the pageDocument to be indexed are known
     *
     * @param Document $pageDocument the generated page document
     * @param TypoScriptFrontendController $page the page object with information about page id or language
     *
     * @throws DBALDriverException
     * @noinspection PhpUnused
     */
    public function postProcessPageDocument(Document $pageDocument, TypoScriptFrontendController $page)
    {
        $accessField = $pageDocument['access'];
        /* @var Rootline $pageAccessRootline */
        $pageAccessRootline = GeneralUtility::makeInstance(Rootline::class, $accessField);
        $this->addDetectedFilesToPage($page, $pageAccessRootline);
    }

    /**
     * Adds detected files to index queue
     *
     * @param TypoScriptFrontendController $page the page object with information about page id or language
     * @param Rootline $pageAccessRootline
     * @throws DBALDriverException
     * @throws Exception
     */
    protected function addDetectedFilesToPage(TypoScriptFrontendController $page, Rootline $pageAccessRootline)
    {
        $site = $this->getSiteRepository()->getSiteByPageId($page->id);
        $successfulFileUids = (array)$this->getRegistry()->get('tx_solrfal', 'pageContextDetector.successfulFileUids', []);

        /* @var Logger $logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $logger->info('Adding trigger indexing files for page ' . $page->id . ' with access rights ' . $pageAccessRootline);

        /* @var PageContextDetector $pageContextDetector */
        $pageContextDetector = GeneralUtility::makeInstance(PageContextDetector::class, $site);
        $successfulFileUids = array_unique(array_merge(
            $successfulFileUids,
            $pageContextDetector->addDetectedFilesToPage($page, $pageAccessRootline, static::$collectedFileUids, static::$collectedContentElements, $successfulFileUids)
        ));

        // store successful file uids as they are required for indexing further variants of this page
        $this->getRegistry()->set('tx_solrfal', 'pageContextDetector.successfulFileUids', $successfulFileUids);
    }

    /**
     * Returns a registry instance
     *
     * @return Registry
     */
    protected function getRegistry(): Registry
    {
        return GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * Returns a site repository instance
     *
     * @return SiteRepository
     */
    protected function getSiteRepository(): SiteRepository
    {
        return GeneralUtility::makeInstance(SiteRepository::class);
    }

    /**
     * Reset the successful file uids
     *
     * Successfully detected file uids were stored in registry
     * while indexing all variants of a page. After completing the
     * indexing of the item the uids must be cleared.
     * @noinspection PhpUnused
     */
    public function resetSuccessfulFileUids()
    {
        $this->getRegistry()->remove('tx_solrfal', 'pageContextDetector.successfulFileUids');
    }

    /**
     * Hook for post-processing the initialization of ContentObjectRenderer
     * Passes the record
     *
     * @param ContentObjectRenderer $parentObject Parent content object
     * @noinspection PhpUnused
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection, is defined in TYPO3 sources
     */
    public function postProcessContentObjectInitialization(ContentObjectRenderer &$parentObject)
    {
        if (empty($parentObject->currentRecord) || empty($parentObject->data)) {
            return;
        }
        list($table, $uid) = explode(':', $parentObject->currentRecord);
        if ($table === 'tt_content') {
            /* @var Logger $logger */
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->info('postInitHook called for record: ' . $parentObject->currentRecord);
            static::$collectedContentElements[$uid] = $parentObject->data;
        }
    }
}
