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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\Detection;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetector;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class PageContextDetectorTest
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class PageContextDetectorTest extends UnitTest
{
    /**
     * @test
     */
    public function addDetectedFilesToPageReturnsEmptyArrayWhenPageContextIsDisabled()
    {
        $tsfeMock = $this->getDumbMock(TypoScriptFrontendController::class);
        $rootLineMock = $this->getDumbMock(Rootline::class);
        /** @var PageContextDetector $pageContextDetector */
        $pageContextDetector = $this->getMockBuilder(PageContextDetector::class)->disableOriginalConstructor()->setMethods(['isIndexingEnabledForContext'])->getMock();
        $pageContextDetector->expects(self::once())->method('isIndexingEnabledForContext')->with('page')->willReturn(false);

        $addedUids = $pageContextDetector->addDetectedFilesToPage($tsfeMock, $rootLineMock);
        self::assertSame([], $addedUids, 'Context detector did not return empty array when page context was disabled');
    }
}
