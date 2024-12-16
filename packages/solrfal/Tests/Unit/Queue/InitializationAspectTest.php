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

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrfal\Detection\RecordContextDetector;
use ApacheSolrForTypo3\Solrfal\Queue\InitializationAspect;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class InitializationAspectTest
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class InitializationAspectTest extends UnitTest
{
    /**
     * @test
     */
    public function postProcessIndexQueueInitializationCallsInitializeOfFileIndexQueue()
    {
        $testConfig = [];

        /* @var InitializationAspect|MockObject $fixture */
        $fixture = $this->getAccessibleMock(InitializationAspect::class, ['getContextDetectorsForSite']);
        $site = $this->getAccessibleMock(Site::class, [], [], '', false);

        /* @var RecordContextDetector|MockObject $recordContextMock */
        $recordContextMock = $this->getAccessibleMock(RecordContextDetector::class, [], [], '', false);
        $recordContextMock->expects(self::once())->method('initializeQueue')->with($testConfig);
        $fixture->expects(self::once())->method('getContextDetectorsForSite')->willReturn([$recordContextMock]);

        $fixture->postProcessIndexQueueInitialization($site, $testConfig, []);
    }
}
