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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\System\Configuration;

use ApacheSolrForTypo3\Solrfal\System\Links\FileLinkExtractionService;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class ExtensionConfigurationTest
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class FileLinkExtractionServiceTest extends UnitTest
{
    public function extractLinksDataProvider()
    {
        return  [
            'tt_content bodytext link' => ['<p>aaa <a href="t3://file?uid=76" target="_top" title="blabla">testfile</a> aaa</p> ', ['t3://file?uid=76']],
            'tt_content header_link' => ['t3://file?uid=76 _blank - test', ['t3://file?uid=76']],
        ];
    }

    /**
     * @dataProvider extractLinksDataProvider
     * @test
     */
    public function testCanExtractLinksFromContent($content, $expectedLinks)
    {
        $fileLinkExtracter = new FileLinkExtractionService();
        $links = $fileLinkExtracter->extract($content);
        self::assertSame($expectedLinks, $links);
    }
}
