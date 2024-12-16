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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Detection;

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetectorAspectInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TestAspect implements PageContextDetectorAspectInterface
{
    /**
     * @param array $fileUids
     * @param TypoScriptFrontendController $page
     * @param Site $site
     * @return array
     */
    public function addForcedFilesOnPage(array $fileUids, TypoScriptFrontendController $page, Site $site): array
    {
        //returns a fixed fake file uid just for testing
        $fileUids[] = 11111;
        return $fileUids;
    }
}
