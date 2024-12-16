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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\Queue;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\Domain\Variants\IdBuilder;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solrfal\Context\PageContext;
use ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\System\Environment\FrontendServerEnvironment;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Resource\File;

class DocumentFactoryTest extends UnitTest
{
    use ProphecyTrait;

    /**
     * @test
     * @noinspection PhpUndefinedFieldInspection
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     * @noinspection PhpParamsInspection
     */
    public function canSetDefaultsOnAccessFieldNullOrFalse()
    {
        $solrDocument = new Document();
        $fileIndexQueueItemMock = $this->getDumbMock(Item::class);
        $documentFactoryMock = $this->getDocumentFactoryPartialMockFor_addContextInformation_method();

        $solrDocument->setField('access', null);
        $documentFactoryMock->callProtectedAddContextInformation($solrDocument, $fileIndexQueueItemMock);
        self::assertEquals('c:0', $solrDocument->access, 'Default value for access field can not be set if document.access field value is null.');

        $solrDocument->setField('access', false);
        $documentFactoryMock->callProtectedAddContextInformation($solrDocument, $fileIndexQueueItemMock);
        self::assertEquals('c:0', $solrDocument->access, 'Default value for access field can not be set if document.access field value is false.');
    }

    /**
     * @test
     * @noinspection PhpUndefinedFieldInspection
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     * @noinspection PhpUndefinedMethodInspection
     */
    public function canTransferAccessRightsFromPageContext()
    {
        $expectedAccessDefinition = '82:-2/c:0';
        $fileDumbMock = $this->getDumbMock(File::class);
        $siteDumbMock = $this->getDumbMock(Site::class);

        $rootLine = $this->prophesize(Rootline::class);
        $rootLine
            ->__toString()->willReturn($expectedAccessDefinition);

        $pageContext = $this->prophesize(PageContext::class);
        $pageContext
            ->getAccessRestrictions()->willReturn($rootLine);
        $pageContext
            ->getAdditionalStaticDocumentFields()->willReturn([]);
        $pageContext
            ->getAdditionalDynamicDocumentFields($fileDumbMock)->willReturn([[]]);
        $pageContext
            ->getPageId()->willReturn(82);
        $pageContext
            ->getSite()->willReturn($siteDumbMock);

        $fileIndexQueueItem = $this->prophesize(Item::class);
        $fileIndexQueueItem
            ->getContext()->willReturn($pageContext);
        $fileIndexQueueItem
            ->getFile()->willReturn($fileDumbMock);
        $fileIndexQueueItem->getUid()->willReturn(3);

        $documentFactoryPartialMock = $this->getDocumentFactoryPartialMockFor_addContextInformation_method();

        $solrDocument = new Document();
        $solrDocument->setField('access', null);
        $documentFactoryPartialMock->callProtectedAddContextInformation($solrDocument, $fileIndexQueueItem->reveal());
        self::assertEquals($expectedAccessDefinition, $solrDocument->access, 'Default value for access field can not be set if document.access field is null.');
    }

    /**
     * @return DocumentFactory
     */
    private function getDocumentFactoryPartialMockFor_addContextInformation_method(): DocumentFactory
    {
        return new class($this->getDumbMock(IdBuilder::class), $this->getDumbMock(FrontendServerEnvironment::class)) extends DocumentFactory {
            public function callProtectedAddContextInformation(Document $document, Item $item)
            {
                return $this->addContextInformation($document, $item);
            }
            protected function calculateDocumentId(Item $item): string
            {
                return '';
            }
        };
    }
}
