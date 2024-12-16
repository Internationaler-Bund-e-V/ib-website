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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Context;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solrfal\Context\PageContext;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Exception as TestingFrameworkCoreException;

/**
 * Page context tests
 *
 * @author Markus Friedrich
 */
class PageContextTest extends IntegrationTest
{
    /**
     * @throws NoSuchCacheException
     * @throws DBALException
     * @throws TestingFrameworkCoreException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
    }

    /**
     * Returns the initialized page context
     *
     * @return PageContext
     */
    protected function getPageContext(): PageContext
    {
        $site = GeneralUtility::makeInstance(SiteRepository::class)->getSiteByRootPageId(1);
        return GeneralUtility::makeInstance(
            PageContext::class,
            $site,
            GeneralUtility::makeInstance(Rootline::class),
            1, // page uid
            'tt_content',
            'bodytext',
            10, // content element uid
            1 // language
        );
    }

    /**
     * @test
     * @throws TestingFrameworkCoreException
     * @throws FileDoesNotExistException
     */
    public function detectsTranslatedFileReferenceTitle()
    {
        $this->importDataSetFromFixture('detects_in_translated_page.xml');

        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        // get dummy file
        $file = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject(8888);
        self::assertInstanceOf(File::class, $file);

        // check additional fields
        $pageContext = $this->getPageContext();
        $dynamicFields = $pageContext->getAdditionalDynamicDocumentFields($file);
        self::assertEquals('Hallo Solr', $dynamicFields['fileReferenceTitle'], 'File reference title is not "Hallo Solr" as expected: ' . $dynamicFields['fileReferenceTitle']);
    }
}
