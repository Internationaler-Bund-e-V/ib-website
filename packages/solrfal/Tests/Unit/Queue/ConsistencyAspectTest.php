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

use ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class InitializationAspectTest
 *
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class ConsistencyAspectTest extends UnitTest
{
    /**
     * @test
     */
    public function fileIndexRecordUpdatedCallsIssueCommandOnDetectorsMethodWithRequiredArguments()
    {
        //self::markTestSkipped('May be obsolete with new events implementation.');
        $consistencyAspect = $this->getMockBuilder(ConsistencyAspect::class)
            ->onlyMethods(['issueCommandOnDetectors', 'getDetectorsForRecord'])
            ->disableOriginalConstructor()
            ->getMock();

        $consistencyAspect->method('getDetectorsForRecord')->willReturn([]);

        $consistencyAspect->expects(self::once())
            ->method('issueCommandOnDetectors')
            ->with('fileIndexRecordUpdated', 'sys_file', 1);
        $consistencyAspect->fileIndexRecordUpdated(1);
    }
}
