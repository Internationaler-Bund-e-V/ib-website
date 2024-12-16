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
use ApacheSolrForTypo3\Solrfal\Context\AbstractContext;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class InitializationAspectTest
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class AbstractContextTest extends UnitTest
{
    /**
     * @test
     */
    public function gettersReturnConstructorParameters()
    {
        $site = $this->getDumbMock(Site::class);
        $rootline = $this->getDumbMock(Rootline::class);
        $language = 0;

        /** @var \ApacheSolrForTypo3\Solrfal\Context\AbstractContext $fixture */
        $fixture = $this->getAccessibleMock(
            AbstractContext::class,
            ['getContextIdentifier', 'getIdentifierForItemSpecificFieldConfiguration'],
            [$site, $rootline, $language]
        );

        self::assertSame($site, $fixture->getSite());
        self::assertSame($rootline, $fixture->getAccessRestrictions());
        self::assertSame($language, $fixture->getLanguage());
    }

    /**
     * @test
     */
    public function toArrayReturnsExpectedValues()
    {
        $site = $this->getDumbMock(Site::class);
        $site->expects(self::once())->method('getRootPageId')->willReturn(55);

        $rootline = $this->getDumbMock(Rootline::class);
        $rootline->expects(self::once())->method('__toString')->willReturn('c:0');

        $language = 0;

        /** @var \ApacheSolrForTypo3\Solrfal\Context\AbstractContext $fixture */
        $fixture = $this->getAccessibleMock(
            AbstractContext::class,
            ['getContextIdentifier', 'getIdentifierForItemSpecificFieldConfiguration'],
            [$site, $rootline, '', $language]
        );
        $fixture->expects(self::once())->method('getContextIdentifier')->willReturn('test');

        $data = [
            'context_type' => 'test',
            'context_language' => 0,
            'context_access_restrictions' => 'c:0',
            'context_site' => 55,
            'context_additional_fields' => '[]',
            'context_record_indexing_configuration' => '',
            'error' => 0,
            'error_message' => '',
        ];

        self::assertEquals($data, $fixture->toArray());
    }
}
