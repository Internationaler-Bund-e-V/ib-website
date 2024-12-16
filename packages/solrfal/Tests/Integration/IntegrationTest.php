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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration;

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\Tests\Integration\IntegrationTest as SolrIntegrationTest;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Exception as TestingFrameworkCoreException;

/**
 * Base class for all integration tests in the EXT:solrfal project
 *
 * @author Markus Friedrich
 */
abstract class IntegrationTest extends SolrIntegrationTest
{
    protected $coreExtensionsToLoad = [
        'filemetadata',
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/solr',
        'typo3conf/ext/solrfal',
    ];

    /**
     * Temporary files
     *
     * @var array $temporaryFiles
     */
    protected array $temporaryFiles = [];

    /**
     * @throws NoSuchCacheException
     * @throws TestingFrameworkCoreException
     * @throws DBALException|\Doctrine\DBAL\DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);
    }

    protected function tearDown(): void
    {
        $this->removeTemporaryFiles();
        parent::tearDown();
    }

    /**
     * @throws TestingFrameworkCoreException
     * @throws DBALDriverException
     */
    protected function writeDefaultSolrTestSiteConfiguration()
    {
        parent::writeDefaultSolrTestSiteConfiguration();
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '@import \'EXT:solrfal/Tests/Integration/Fixtures/sites_setup_and_data_set/Integration.setup.typoscript\''
        );
        $this->addTypoScriptConstantsToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '@import \'EXT:solrfal/Tests/Integration/Fixtures/sites_setup_and_data_set/Integration.constants.typoscript\''
        );
    }

    /**
     * Returns the data handler
     *
     * @return DataHandler
     */
    protected function getDataHandler(): DataHandler
    {
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        return GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * Place temporary file
     *
     * @param string $fixtureFileName
     * @param string $targetDirectory , e.g. 'fileadmin'
     */
    protected function placeTemporaryFile(string $fixtureFileName, string $targetDirectory)
    {
        // create directory
        $dirPath = Environment::getPublicPath() . '/' . trim($targetDirectory, ' /');
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        // place file
        $filePath =  $dirPath . '/' . $fixtureFileName;
        $fixturePath = $this->getFixturePathByName($fixtureFileName);

        if (copy($fixturePath, $filePath)) {
            $this->temporaryFiles[] = $filePath;
        }

        self::assertTrue(is_file($fixturePath), 'Couldn\'t find source of temporary file:' . $fixturePath);
        self::assertTrue(is_file($filePath), 'Couldn\'t place temporary file: ' . $filePath);
    }

    /**
     * Removes temporary files
     */
    protected function removeTemporaryFiles()
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            unlink($temporaryFile);
        }
        $this->temporaryFiles = [];
    }

    /**
     * Returns the storage context detector
     *
     * @param int $rootPageId
     * @return Site
     * @throws DBALDriverException
     */
    protected function getSite(int $rootPageId): Site
    {
        $siteRepository = GeneralUtility::makeInstance(SiteRepository::class);
        return $siteRepository->getSiteByRootPageId($rootPageId);
    }

    /**
     * @return SiteRepository
     */
    protected function getSiteRepository(): SiteRepository
    {
        return GeneralUtility::makeInstance(SiteRepository::class);
    }

    /**
     * @return ItemRepository
     */
    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }
}
