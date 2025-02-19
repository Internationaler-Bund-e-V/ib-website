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
use ApacheSolrForTypo3\Solr\Exception\InvalidArgumentException;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solrfal\EventListener\ResourceEventListener;
use ApacheSolrForTypo3\Solrfal\Exception\Detection\InvalidHookException;
use ApacheSolrForTypo3\Solrfal\Exception\Service\InvalidHookException as InvalidServiceHookException;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class PageContextDetectorFrontendIndexingAspect
 */
class PageContextDetectorFrontendIndexingAspect implements ContentObjectPostInitHookInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * File UIDs, which are linked in processed content elements
     * @var int[]
     */
    public static array $collectedFileUids = [];

    /**
     * Content elements processed
     * @var array<string, string|int|bool>[]
     */
    public static array $collectedContentElements = [];

    /**
     * Registers file uids
     *
     * Used by {@link ResourceEventListener} for {@link \TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent} dispatched by {@link ResourceStorage::getPublicUrl()}
     */
    public static function registerGeneratedPublicUrl(
        ResourceInterface|AbstractFile|FileReference|ProcessedFile $resourceObject,
    ): void {
        if (
            $resourceObject instanceof FileReference
            || $resourceObject instanceof ProcessedFile
            || method_exists($resourceObject, 'getOriginalFile')
        ) {
            static::$collectedFileUids[] = $resourceObject->getOriginalFile()->getUid();
        } elseif ($resourceObject instanceof File) {
            static::$collectedFileUids[] = $resourceObject->getUid();
        }

        /** @var LogManager $loggerManager */
        $loggerManager = GeneralUtility::makeInstance(LogManager::class);
        $logger = $loggerManager->getLogger(__CLASS__);
        $logger->info('getPublicUrl called for file: ' . $resourceObject->getIdentifier());
    }

    /**
     * Allows Modification of the PageDocument
     * Can be used to trigger actions when all contextual variables of the pageDocument to be indexed are known
     *
     * @param Document $pageDocument the generated page document
     * @param TypoScriptFrontendController $tsfe the page object with information about page id or language
     *
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws InvalidHookException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws UnknownLinkHandlerException
     */
    public function postProcessPageDocument(Document $pageDocument, TypoScriptFrontendController $tsfe): void
    {
        $accessField = $pageDocument['access'];
        /** @var Rootline $pageAccessRootline */
        $pageAccessRootline = GeneralUtility::makeInstance(Rootline::class, $accessField);
        $this->addDetectedFilesToPage($tsfe, $pageAccessRootline);
    }

    /**
     * Adds detected files to index queue
     *
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws InvalidHookException
     * @throws InvalidServiceHookException
     * @throws ResourceDoesNotExistException
     * @throws UnknownLinkHandlerException
     */
    protected function addDetectedFilesToPage(TypoScriptFrontendController $tsfeObject, Rootline $pageAccessRootline): void
    {
        $site = $this->getSiteRepository()->getSiteByPageId($tsfeObject->id);
        $successfulFileUids = (array)$this->getRegistry()->get('tx_solrfal', 'pageContextDetector.successfulFileUids', []);

        $this->logger->info('Adding trigger indexing files for page ' . $tsfeObject->id . ' with access rights ' . $pageAccessRootline);

        /** @var PageContextDetector $pageContextDetector */
        $pageContextDetector = GeneralUtility::makeInstance(PageContextDetector::class, $site);
        $successfulFileUids = array_unique(array_merge(
            $successfulFileUids,
            $pageContextDetector->addDetectedFilesToPage($tsfeObject, $pageAccessRootline, static::$collectedFileUids, static::$collectedContentElements, $successfulFileUids)
        ));

        // store successful file uids as they are required for indexing further variants of this page
        $this->getRegistry()->set('tx_solrfal', 'pageContextDetector.successfulFileUids', $successfulFileUids);
    }

    /**
     * Returns a registry instance
     */
    protected function getRegistry(): Registry
    {
        return GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * Returns a site repository instance
     */
    protected function getSiteRepository(): SiteRepository
    {
        return GeneralUtility::makeInstance(SiteRepository::class);
    }

    /**
     * Hook for post-processing the initialization of ContentObjectRenderer
     * Passes the record
     *
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection, is defined in TYPO3 sources
     */
    public function postProcessContentObjectInitialization(ContentObjectRenderer &$parentObject): void
    {
        if (empty($parentObject->currentRecord) || empty($parentObject->data)) {
            return;
        }
        list($table, $uid) = explode(':', $parentObject->currentRecord);
        if ($table === 'tt_content') {
            $this->logger->info('postInitHook called for record: ' . $parentObject->currentRecord);
            static::$collectedContentElements[$uid] = $parentObject->data;
        }
    }
}
