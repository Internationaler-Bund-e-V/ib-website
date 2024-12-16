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

use ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\SchemaException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\Schema\Exception\StatementException;
use TYPO3\CMS\Core\Database\Schema\Exception\UnexpectedSignalReturnValueTypeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Exception as TestingFrameworkCoreException;

/**
 * Record context detector tests
 *
 * @author Markus Friedrich
 */
class RecordContextDetectorTest extends IntegrationTest
{
    /**
     * @throws NoSuchCacheException
     * @throws DBALException
     * @throws SchemaException
     * @throws StatementException
     * @throws UnexpectedSignalReturnValueTypeException
     * @throws TestingFrameworkCoreException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.queue {
                news = 1
                news {
                    table = tx_fakeextension_domain_model_news
                    attachments = 1
                    attachments {
                        fields = bodytext
                        fileExtensions = pdf,txt
                    }
                }
            }
            '
        );

        $this->importExtTablesDefinition('RecordContext/fake_extension_table.sql');
        $GLOBALS['TCA']['tx_fakeextension_domain_model_news'] = include($this->getFixturePathByName('RecordContext/fake_extension_tca.php'));
    }

    /**
     * @test
     */
    public function detectFileInRecordAfterRecordUpdate()
    {
        $this->importDataSetFromFixture('RecordContext/detects_file_in_record_context.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations('update', 'tx_fakeextension_domain_model_news', 1, [], $dataHandler);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'Number of file index queue entries is not as expected');
        self::assertEquals(9999, $indexQueueContent[0]->getFile()->getUid(), 'Wrong file detected');
        self::assertEquals(
            [
                'context_type' => 'record',
                'context_language' => 0,
                'context_access_restrictions' => 'r:1',
                'context_site' => 1,
                'context_additional_fields' => '[]',
                'context_record_indexing_configuration' => 'news',
                'error' => 0,
                'error_message' => '',
                'context_record_table' => 'tx_fakeextension_domain_model_news',
                'context_record_uid' => 1,
                'context_record_field' => 'bodytext',
            ],
            $indexQueueContent[0]->getContext()->toArray(),
            'Invalid index queue entry found'
        );

        $this->removeTemporaryFiles();
    }

    /**
     * @test
     */
    public function detectFileInRecordAfterTranslatedRecordUpdate()
    {
        $this->importDataSetFromFixture('RecordContext/detects_file_in_record_context_for_translation.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations('update', 'tx_fakeextension_domain_model_news', 1, [], $dataHandler);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(2, $indexQueueContent, 'Number of file index queue entries is not as expected');

        $secondItem = $indexQueueContent[1];
        self::assertEquals(9999, $secondItem->getFile()->getUid(), 'Wrong file detected');

        $contextOfSecondRecord = $secondItem->getContext();
        $contextArray = $contextOfSecondRecord->toArray();
        $expectedContextArray = [
            'context_type' => 'record',
            'context_language' => 1,
            'context_access_restrictions' => 'r:0',
            'context_site' => 1,
            'context_additional_fields' => '[]',
            'context_record_indexing_configuration' => 'news',
            'error' => 0,
            'error_message' => '',
            'context_record_table' => 'tx_fakeextension_domain_model_news',
            'context_record_uid' => 1,
            'context_record_field' => 'bodytext',
        ];

        self::assertEquals($expectedContextArray, $contextArray, 'Invalid index queue entry found');
        $this->removeTemporaryFiles();
    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    public function queuesSuitableLanguageOnly()
    {
        $this->importDataSetFromFixture('RecordContext/queues_suitable_language_only.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations('update', 'tx_fakeextension_domain_model_news', 2, [], $dataHandler);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'File index queue contains to many entries, please check the behaviour.');

        self::assertEquals(9999, $indexQueueContent[0]->getFile()->getUid(), 'Wrong file in index queue detected.');

        $contextOfSecondRecord = $indexQueueContent[0]->getContext();
        $contextArray = $contextOfSecondRecord->toArray();
        $expectedContextArray = [
            'context_type' => 'record',
            'context_language' => 1,
            'context_access_restrictions' => 'r:0',
            'context_site' => 1,
            'context_additional_fields' => '[]',
            'context_record_indexing_configuration' => 'news',
            'error' => 0,
            'error_message' => '',
            'context_record_table' => 'tx_fakeextension_domain_model_news',
            'context_record_uid' => 1,
            'context_record_field' => 'bodytext',
        ];

        self::assertEquals($expectedContextArray, $contextArray, 'Invalid index queue entry found');
        $this->removeTemporaryFiles();
    }

    /**
     * This test case is based on file extension.
     * The data has .pdf file, but only .doc files are allowed.
     * @test
     */
    public function ignoreNotListedFileExtensionsInRecordAfterRecordUpdate()
    {
        $this->importDataSetFromFixture('RecordContext/no_valid_files_in_record_context.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.enableFileIndexing.recordContext.fileExtensions = doc
            plugin.tx_solr.index.queue.news.attachments.fileExtensions >
            '
        );

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations(
            'update',
            'tx_fakeextension_domain_model_news',
            1,
            [],
            $dataHandler
        );

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEmpty($indexQueueContent, 'Index queue is not empty as expected');

        $this->removeTemporaryFiles();
    }

    /**
     * @test
     */
    public function ignoreMissingFileInRecordAfterRecordUpdate()
    {
        $this->importDataSetFromFixture('RecordContext/ignore_missing_file_in_record_context.xml');

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations('update', 'tx_fakeextension_domain_model_news', 1, [], $dataHandler);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEmpty($indexQueueContent, 'Index queue not is not empty as expected, missing file(s) added');
    }

    /**
     * @test
     */
    public function detectRecordDeletion()
    {
        $this->importDataSetFromFixture('RecordContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start([], ['tx_fakeextension_domain_model_news' => [1 => ['delete' => 1]]]);
        $dataHandler->process_cmdmap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEmpty($indexQueueContent, 'Index queue not is not empty as expected, attached files not removed on record deletion');
    }

    /**
     * @test
     */
    public function detectRecordHiding()
    {
        $this->importDataSetFromFixture('RecordContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start(['tx_fakeextension_domain_model_news' => [1 => ['hidden' => 1]]], []);
        $dataHandler->process_datamap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertEmpty($indexQueueContent, 'Index queue not is not empty as expected, attached files not removed on record hiding');
    }

    /**
     * @test
     */
    public function detectFileRelationDeletion()
    {
        $this->importDataSetFromFixture('RecordContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $dataHandler = $this->getDataHandler();
        $dataHandler->start([], ['sys_file_reference' => [8888 => ['delete' => 1]]]);
        $dataHandler->process_cmdmap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'Index queue not updated as expected, deletion of sys_file_reference ignored.');
    }

    /**
     * @test
     */
    public function detectFileRelationHiding()
    {
        $this->importDataSetFromFixture('RecordContext/observes_deletions.xml');
        $this->placeTemporaryFile('file8888.pdf', 'fileadmin');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
                plugin.tx_solr.index.queue.news.attachments.fields = bodytext,fal_related_files
            '
        );

        $dataHandler = $this->getDataHandler();
        $dataHandler->start(['sys_file_reference' => [8888 => ['hidden' => 1]]], []);
        $dataHandler->process_datamap();

        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'Index queue not updated as expected, hiding of sys_file_reference ignored.');
    }

    /**
     * @dataProvider getGroupSettingVariants
     * @test
     * @param bool $groupFieldDefined
     * @param string $expectedAccessRestrictions
     */
    public function respectsGroupSettings($groupFieldDefined, $expectedAccessRestrictions)
    {
        $this->importDataSetFromFixture('RecordContext/detects_file_in_record_context.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        // remove group field definition if requested
        if (!$groupFieldDefined) {
            unset($GLOBALS['TCA']['tx_fakeextension_domain_model_news']['ctrl']['enablecolumns']['fe_group']);
        }

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations('update', 'tx_fakeextension_domain_model_news', 1, [], $dataHandler);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'Number of file index queue entries is not as expected');
        $accessRestrictions = $indexQueueContent[0]->getContext()->getAccessRestrictions()->__toString();
        self::assertEquals($expectedAccessRestrictions, $accessRestrictions, 'Access restrictions differ from expected value');

        $this->removeTemporaryFiles();
    }

    /**
     * Checks if the RecordContextDetector can handle muliple sites
     *
     * Files from site A mustn't be detected for site B on
     * record updates
     *
     * @test
     */
    public function canHandleMultipleSites()
    {
        $this->importDataSetFromFixture('RecordContext/can_handle_multiple_sites.xml');
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        // simulate update on news record
        $dataHandler = $this->getDataHandler();
        $this->getConsistencyAspect()->processDatamap_afterDatabaseOperations('update', 'tx_fakeextension_domain_model_news', 1, [], $dataHandler);

        // check index queue
        $indexQueueContent = $this->getItemRepository()->findAll();
        self::assertCount(1, $indexQueueContent, 'File index queue should exactly contain one entry');
        if (count($indexQueueContent) == 1) {
            self::assertEquals(1, $indexQueueContent[0]->getContext()->getSite()->getRootPageId(), 'File index queue item must belong to root page 1');
        }

        $this->removeTemporaryFiles();
    }

    /**
     * Returns the group setting variants
     *
     * @return array
     */
    public function getGroupSettingVariants()
    {
        return [
            'group_field_defined' => [true,  'r:1'],
            'no_group_field_defined' => [false, 'r:0'],
        ];
    }

    /**
     * Returns the ConsistencyAspect instance.
     *
     * @return ConsistencyAspect
     */
    protected function getConsistencyAspect(): ConsistencyAspect
    {
        return GeneralUtility::makeInstance(ConsistencyAspect::class);
    }
}
