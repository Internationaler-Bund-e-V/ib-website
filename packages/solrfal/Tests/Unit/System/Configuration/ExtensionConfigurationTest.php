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

use ApacheSolrForTypo3\Solrfal\System\Configuration\ExtensionConfiguration;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class ExtensionConfigurationTest
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class ExtensionConfigurationTest extends UnitTest
{
    /**
     * @test
     */
    public function testGetIsSiteExclusiveRecordTable()
    {
        $configuration = new ExtensionConfiguration([
            'siteExclusiveRecordTables' => 'pages, pages_language_overlay, tt_content, sys_file_reference',
        ]);

        self::assertFalse($configuration->getIsSiteExclusiveRecordTable('tx_news_domain_model_news'));
        self::assertTrue($configuration->getIsSiteExclusiveRecordTable('pages'));
    }
}
