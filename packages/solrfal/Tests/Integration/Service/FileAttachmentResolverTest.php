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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Service;

use ApacheSolrForTypo3\Solrfal\Service\FileAttachmentResolver;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InitializationAspectTest
 */
class FileAttachmentResolverTest extends IntegrationTest
{
    /**
     * @var array
     */
    protected $globalsBackup;

    /**
     * @var FileAttachmentResolver
     */
    protected $fileAttachmentResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileAttachmentResolver = GeneralUtility::makeInstance(FileAttachmentResolver::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function detectFilesInFieldCanBeModifiedWithAHook()
    {
        $this->globalsBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal'];
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->importDataSetFromFixture('detects_file_in_ttcontent.xml');

        $hookClass = TestFileAttachmentResolverProcessor::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect'][] = $hookClass;

        $uids = $this->fileAttachmentResolver->detectFilesInField('tt_content', 'bodytext', ['bodytext' => 'this is a test <link t3://file?uid=8888>']);

        self::assertContains(8888, $uids);
        self::assertContains(9999, $uids);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal'] = $this->globalsBackup;
    }

    /**
     * @test
     */
    public function canDetectFilesWithNewLinkSyntaxInLinkTag()
    {
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->importDataSetFromFixture('detects_file_in_ttcontent.xml');

        $uids = $this->fileAttachmentResolver->detectFilesInField('tt_content', 'bodytext', ['bodytext' => 'this is a test <link t3://file?uid=8888>my link</link>']);

        self::assertContains(8888, $uids);
    }

    /**
     * @test
     */
    public function canDetectFilesWithNewLinkSyntaxInATag()
    {
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->importDataSetFromFixture('detects_file_in_ttcontent.xml');

        $uids = $this->fileAttachmentResolver->detectFilesInField('tt_content', 'bodytext', ['bodytext' => 'this is a test  <a href="t3://file?uid=8888">my link</a>']);

        self::assertContains(8888, $uids);
    }

    /**
     * @test
     */
    public function detectFilesInFieldCanExtractFileFromFileLinkInContentWhenNoHookRegistered()
    {
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->importDataSetFromFixture('detects_file_in_ttcontent.xml');

        self::assertEmpty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['FileAttachmentResolverAspect']);
        $uids = $this->fileAttachmentResolver->detectFilesInField('tt_content', 'bodytext', ['bodytext' => 'this is a test <link t3://file?uid=8888>']);

        self::assertContains(8888, $uids);
    }
}
