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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\Context;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solrfal\Context\StorageContext;
use ApacheSolrForTypo3\Solrfal\Detection\StorageContextDetector;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;

/**
 * Class InitializationAspectTest
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class StorageContextTest extends UnitTest
{
    /**
     * @var \ApacheSolrForTypo3\Solrfal\Context\StorageContext
     */
    protected $fixture;

    protected function setUp(): void
    {
        $site = $this->getDumbMock(Site::class);
        $site->expects(self::any())->method('getRootPageId')->willReturn(55);

        $rootline = $this->getDumbMock(Rootline::class);
        $rootline->expects(self::any())->method('__toString')->willReturn('c:0');

        $this->fixture = new StorageContext($site, $rootline, 'fileadmin', 0);
    }

    /**
     * @test
     */
    public function getContextIdentifierReturnsStorage()
    {
        self::assertEquals('storage', $this->fixture->getContextIdentifier());
    }

    /**
     * @test
     */
    public function toArrayContainsCorrectType()
    {
        self::assertArrayHasKey('context_type', $this->fixture->toArray());
        self::assertContains('storage', $this->fixture->toArray());
    }

    /**
     * @test
     */
    public function toArrayContainsIndexConfigurationName()
    {
        $storageData = $this->fixture->toArray();
        self::assertArrayHasKey('context_record_indexing_configuration', $storageData);
        self::assertSame('fileadmin', $storageData['context_record_indexing_configuration']);
    }

    /**
     * @test
     */
    public function initializeQueueTriggersInitializeQueueForStorage()
    {
        $fakeConfiguration = [
            'fileadmin.' => [
                'table' => 'sys_file_storage',
                'storageUid' => 4711,
            ],
        ];

        $configurationMock = $this->getDumbMock(TypoScriptConfiguration::class);
        $configurationMock->expects(self::once())->method('getObjectByPathOrDefault')->willReturn(
            $fakeConfiguration
        );

        /** @var $siteMock Site */
        $siteMock = $this->getDumbMock(Site::class);
        $siteMock->expects(self::once())->method('getSolrConfiguration')->willReturn(
            $configurationMock
        );

        $storageMock = $this->getDumbMock(ResourceStorage::class);
        $storageRepositoryMock = $this->getDumbMock(StorageRepository::class);
        $storageRepositoryMock->expects(self::once())->method('findByUid')->willReturn($storageMock);

        $itemRepositoryMock = $this->getDumbMock(ItemRepository::class);

        // we fake the storageContext, that the indexing is enabled and a mocked logger will be used
        // in addition we mock initializeQueueForStorage to only check if it this method was triggered
        /** @var $storageContext StorageContextDetector */
        $storageContext = $this->getMockBuilder(StorageContextDetector::class)->setMethods(
            ['getLogger', 'initializeQueueForStorage', 'isIndexingEnabledForContext', 'isIndexingEnabledForStorage', 'getStorageRepository', 'getItemRepository']
        )->setConstructorArgs([$siteMock])->getMock();
        $storageContext->expects(self::once())->method('isIndexingEnabledForStorage')->willReturn(true);
        $storageContext->expects(self::once())->method('isIndexingEnabledForContext')->with('storage')->willReturn(true);
        $storageContext->expects(self::any())->method('getLogger')->willReturn(
            $this->getDumbMock(Logger::class)
        );
        $storageContext->expects(self::any())->method('getStorageRepository')->willReturn(
            $storageRepositoryMock
        );
        $storageContext->expects(self::any())->method('getItemRepository')->willReturn(
            $itemRepositoryMock
        );

        $storageContext->expects(self::once())->method('initializeQueueForStorage')->with($storageMock, 'fileadmin');

        $fakeStatus = ['fileadmin' => 1];
        $storageContext->initializeQueue($fakeStatus);
    }
}
