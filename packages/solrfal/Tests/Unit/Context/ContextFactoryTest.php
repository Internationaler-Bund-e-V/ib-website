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
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solrfal\Context\ContextFactory;
use ApacheSolrForTypo3\Solrfal\Context\ContextFactoryInterface;
use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use ApacheSolrForTypo3\Solrfal\Context\PageContext;
use ApacheSolrForTypo3\Solrfal\Context\RecordContext;
use ApacheSolrForTypo3\Solrfal\Context\StorageContext;
use ApacheSolrForTypo3\Solrfal\Detection\RecordDetectionInterface;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;
use function array_merge;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InitializationAspectTest
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class ContextFactoryTest extends UnitTest
{
    /**
     * See definition of table tx_solr_indexqueue_file in ext_tables.sql
     */
    protected const DEFAULT_VALUES_TABLE_QUEUE_FILE = [
        'uid' => 1,

        'last_update' => 0,
        'last_indexed' => 0,

        'file' => 0,
        'merge_id' => '',

        'context_type' => '',
        'context_site' => 0,
        'context_access_restrictions' => 'c:0',
        'context_language' => 0,

        'context_record_indexing_configuration' => '',
        'context_record_uid' => 0,
        'context_record_table' => '',
        'context_record_field' => '',
        'context_record_page' => 0,
        'context_additional_fields' => '',

        'error_message' => '',
        'error' => 0,
    ];

    /**
     * @var ContextFactory
     */
    protected $fixture;

    /**
     * @var MockObject
     */
    protected $siteRepository;

    protected function setUp(): void
    {
        $this->siteRepository = $this->getDumbMock(SiteRepository::class);
        $this->fixture = new ContextFactory($this->siteRepository);
    }

    /**
     * @test
     */
    public function registerTypeThrowsExceptionIfClassIsNotImplementingContextInterface()
    {
        $this->expectException(\RuntimeException::class);
        $this->fixture->registerType('newType', __CLASS__, __CLASS__);
    }

    /**
     * @test
     */
    public function registerTypeWorksIfInterfaceIsImplemented()
    {
        $this->fixture->registerType(
            'newType',
            get_class($this->getDumbMock(ContextInterface::class)),
            get_class($this->getDumbMock(RecordDetectionInterface::class))
        );
    }

    /**
     * @test
     */
    public function registerTypeThrowsExceptionForCustomFactoryNotImplementingTheInterface()
    {
        $this->expectException(\RuntimeException::class);
        $this->fixture->registerType(
            'newType',
            get_class($this->getDumbMock(ContextInterface::class)),
            __CLASS__
        );
    }

    /**
     * @test
     */
    public function registerTypeAcceptsCorrectFactory()
    {
        $this->fixture->registerType(
            'newType',
            get_class($this->getDumbMock(ContextInterface::class)),
            get_class($this->getDumbMock(RecordDetectionInterface::class)),
            get_class($this->getDumbMock(ContextFactoryInterface::class))
        );
    }

    /**
     * @test
     */
    public function getByRecordThrowsExceptionForUnsupportedType()
    {
        $this->expectException(\RuntimeException::class);
        $this->fixture->getByRecord(['context_type' => 'foo']);
    }

    /**
     * @test
     */
    public function getByRecordUsesCustomFactoryIfRegistered()
    {
        $record = ['context_type' => 'foo'];

        $class = get_class($this->getDumbMock(ContextInterface::class));
        $factory = $this->getDumbMock(ContextFactoryInterface::class);
        $factory->expects(self::once())->method('getByRecord')->with($record);
        GeneralUtility::addInstance(get_class($factory), $factory);

        $detectorMock = $this->getDumbMock(RecordDetectionInterface::class);

        $this->fixture->registerType('foo', $class, get_class($detectorMock), get_class($factory));

        $this->fixture->getByRecord($record);

        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     * @dataProvider getContextRecords
     * @param array $record
     * @param string $expectedClass
     */
    public function getByRecordCreatesBuildInContexts(array $record, string $expectedClass)
    {
        $site = $this->getDumbMock(Site::class);
        $site->expects(self::any())->method('getRootPageId')->willReturn(55);
        GeneralUtility::addInstance(Site::class, $site);

        $rootline = $this->getDumbMock(Rootline::class);
        $rootline->expects(self::any())->method('__toString')->willReturn('c:0');

        GeneralUtility::addInstance(Rootline::class, $rootline);

        $this->siteRepository->expects(self::any())->method('getSiteByRootPageId')->willReturn($site);
        self::assertInstanceOf($expectedClass, $this->fixture->getByRecord($record));

        GeneralUtility::purgeInstances();
    }

    /**
     * @return array
     */
    public static function getContextRecords()
    {
        return [
            'Storage' => [array_merge(self::DEFAULT_VALUES_TABLE_QUEUE_FILE, ['context_type' => 'storage']), StorageContext::class],
            'Page' => [array_merge(self::DEFAULT_VALUES_TABLE_QUEUE_FILE, ['context_type' => 'page']), PageContext::class],
            'Record' => [array_merge(self::DEFAULT_VALUES_TABLE_QUEUE_FILE, ['context_type' => 'record']), RecordContext::class],
        ];
    }
}
