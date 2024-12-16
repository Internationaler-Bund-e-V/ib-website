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
use ApacheSolrForTypo3\Solrfal\Context\RecordContext;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class InitializationAspectTest
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class RecordContextTest extends UnitTest
{
    /**
     * @var RecordContext
     */
    protected RecordContext $fixture;

    protected function setUp(): void
    {
        $site = $this->getDumbMock(Site::class);
        $site->expects(self::any())->method('getRootPageId')->willReturn(55);

        $rootline = $this->getDumbMock(Rootline::class);
        $rootline->expects(self::any())->method('__toString')->willReturn('c:0');

        $this->fixture = new RecordContext($site, $rootline, 'tt_news', 'media', 35, '');
    }

    /**
     * @test
     */
    public function getContextIdentifierReturnsRecord()
    {
        self::assertEquals('record', $this->fixture->getContextIdentifier());
    }

    /**
     * @test
     */
    public function constructorCallsParentConstructor()
    {
        self::assertNotNull($this->fixture->getSite());
        self::assertNotNull($this->fixture->getLanguage());
        self::assertNotNull($this->fixture->getAccessRestrictions());
    }

    /**
     * @test
     */
    public function constructorInitializesVariables()
    {
        self::assertEquals('tt_news', $this->fixture->getTable());
        self::assertEquals('media', $this->fixture->getField());
        self::assertEquals(35, $this->fixture->getUid());
    }

    /**
     * @test
     */
    public function toArrayContainsCorrectType()
    {
        self::assertArrayHasKey('context_type', $this->fixture->toArray());
        self::assertContains('record', $this->fixture->toArray());
    }

    /**
     * @test
     */
    public function toArrayContainsNewProperties()
    {
        $array = $this->fixture->toArray();
        self::assertArrayHasKey('context_record_table', $array);
        self::assertArrayHasKey('context_record_field', $array);
        self::assertArrayHasKey('context_record_uid', $array);
    }
}
