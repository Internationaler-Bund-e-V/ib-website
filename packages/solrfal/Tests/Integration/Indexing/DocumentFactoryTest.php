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

namespace ApacheSolrForTypo3\Solrfal\Tests\Integration\Indexing;

use ApacheSolrForTypo3\Solr\FrontendEnvironment\Tsfe;
use ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory;
use ApacheSolrForTypo3\Solrfal\Tests\Integration\IntegrationTest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Document factory tests
 *
 * @author Markus Friedrich
 */
class DocumentFactoryTest extends IntegrationTest
{
    /**
     * Set up document factory tests
     */
    protected function setUp(): void
    {
        $this->testExtensionsToLoad[] = 'typo3/sysext/filemetadata';

        parent::setUp();
        $this->writeDefaultSolrTestSiteConfiguration();
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.queue {
              _FILES {
                # Basic configuration which applies to all files which are indexed, record: sys_file_metadata
                default {
                  fieldDefinedViaGlobalConfiguration_intS = TEXT
                  fieldDefinedViaGlobalConfiguration_intS.value = 1

                  metaDataTitle = TEXT
                  metaDataTitle.field = title
                }
              }
            }
            '
        );

        $this->importExtTablesDefinition('fake_extension_table.sql');
        $GLOBALS['TCA']['tx_fakeextension_domain_model_news'] = include($this->getFixturePathByName('fake_extension_tca.php'));
    }

    /**
     * Tests the document creation in record context
     *
     * @test
     * @noinspection PhpUndefinedFieldInspection
     */
    public function createDocumentInRecordContext(): void
    {
        $this->importDataSetFromFixture('index_file_in_record_context.xml');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.queue {
              _FILES {
                # additional Fields which are indexed, when in recordContext
                recordContext {
                  # for all tables
                  default {
                    fieldDefinedViaGlobalRecordContextConfiguration_intS = TEXT
                    fieldDefinedViaGlobalRecordContextConfiguration_intS.value = 1
                  }
                  # special configuration per table
                  tx_fakeextension_domain_model_news {
                    fieldDefinedViaRecordContextTableConfiguration_intS = TEXT
                    fieldDefinedViaRecordContextTableConfiguration_intS.value = 1
                  }
                }
              }
              news = 1
              news {
                table = tx_fakeextension_domain_model_news
                fields {
                  title = title
                  url = TEXT
                  url.value = fake_route_to_fake_news={field:uid}
                }
                attachments = 1
                attachments {
                  fields = fal_related_files
                  fileExtensions = pdf,txt
                }
              }
            }
            '
        );
        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $queueItem = $this->getItemRepository()->findByUid(1);

        GeneralUtility::makeInstance(Tsfe::class)
            ->getTsfeByPageIdAndLanguageId(1);
        $document = $this->getDocumentFactory()->createDocumentForQueueItem($queueItem);

        self::assertEquals('Lorem ipsum', $document->fileReferenceTitle, 'Reference title not set correctly!');

        self::assertEquals('title sys_file_metadata', $document->metaDataTitle, 'File meta data title not set correctly');
        self::assertTrue(in_array('fileReferenceUrl', $document->getFieldNames()), 'Reference URL not set!');
        self::assertNotEmpty($document->fileReferenceUrl, 'Invalid reference URL!');

        self::assertEquals(1, $document->fieldDefinedViaGlobalConfiguration_intS, 'Field "fieldDefinedViaGlobalConfiguration_intS", defined via global configuration, isn\'t set correctly');
        self::assertEquals(1, $document->fieldDefinedViaGlobalRecordContextConfiguration_intS, 'Field "fieldDefinedViaGlobalRecordContextConfiguration_intS", defined via global record context configuration, isn\'t set correctly');
        self::assertEquals(1, $document->fieldDefinedViaRecordContextTableConfiguration_intS, 'Field "fieldDefinedViaRecordContextTableConfiguration_intS", defined via table specific record context configuration, isn\'t set correctly');
        self::assertEquals('081dbd21084d40f28002304ab2b6b739200c52b9/tx_solr_file/9999', $document->variantId, 'Field "variantId" did not contain expected content');
    }

    /**
     * Tests the document creation in storage context
     *
     * @test
     * @noinspection PhpUndefinedFieldInspection
     */
    public function createDocumentInStorageContext(): void
    {
        $this->importDataSetFromFixture('index_file_in_storage_context.xml');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index.queue {
              _FILES {
                # additional Fields which are indexed, when in storageContext
                storageContext {
                  # for all storages
                  default {
                    fieldDefinedViaGlobalStorageContextConfiguration_intS = TEXT
                    fieldDefinedViaGlobalStorageContextConfiguration_intS.value = 1
                  }
                  # special configuration for storage 1
                  1 {
                    fieldDefinedViaSpecificStorageContextConfiguration_intS = TEXT
                    fieldDefinedViaSpecificStorageContextConfiguration_intS.value = 1
                  }
                }
              }
            }
            '
        );

        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $queueItem = $this->getItemRepository()->findByUid(1);
        GeneralUtility::makeInstance(Tsfe::class)
            ->getTsfeByPageIdAndLanguageId(1);
        $document = $this->getDocumentFactory()->createDocumentForQueueItem($queueItem);

        self::assertEquals('r:1,2', $document->access, 'File permissions not set correctly');
        self::assertEquals('title sys_file_metadata', $document->metaDataTitle, 'File meta data title not set correctly');
        self::assertEquals(1, $document->fieldDefinedViaGlobalConfiguration_intS, 'Field "fieldDefinedViaGlobalConfiguration_intS", defined via global configuration, isn\'t set correctly');
        self::assertEquals(1, $document->fieldDefinedViaGlobalStorageContextConfiguration_intS, 'Field "fieldDefinedViaGlobalStorageContextConfiguration_intS", defined via global storage context configuration, isn\'t set correctly');
        self::assertEquals(1, $document->fieldDefinedViaSpecificStorageContextConfiguration_intS, 'Field "fieldDefinedViaSpecificStorageContextConfiguration_intS", defined via table specific storage context configuration, isn\'t set correctly');
        self::assertEquals('081dbd21084d40f28002304ab2b6b739200c52b9/tx_solr_file/9999', $document->variantId, 'Field "variantId" did not contain expected content');
    }

    /**
     * Tests the document creation in page context
     *
     * @test
     * @noinspection PhpUndefinedFieldInspection
     */
    public function createDocumentInPageContext(): void
    {
        $this->importDataSetFromFixture('index_file_in_page_context.xml');
        $this->addTypoScriptToTemplateRecord(
            1,
            /* @lang TYPO3_TypoScript */
            '
            plugin.tx_solr.index {
              fieldProcessingInstructions.endtime >
              queue {
                _FILES {
                  # additional Fields which are indexed, when in recordContext
                  pageContext {
                    fieldDefinedViaPageContextConfiguration_intS = TEXT
                    fieldDefinedViaPageContextConfiguration_intS.value = 1
                  }
                }
              }
              enableFileIndexing {
                storageContext = 0
                recordContext = 0
                pageContext = 1
                pageContext {
                    contentElementTypes {
                        text = bodytext, header_link
                        textpic < .text
                        uploads = media, file_collections
                    }
                    # restrict indexed files extensions to index
                    fileExtensions = *
                    # Use enable fields from page
                    enableFields {
                        accessGroups >
                        endtime = endtime
                    }
                }
              }
            }
            '
        );

        $this->placeTemporaryFile('file9999.txt', 'fileadmin');

        $queueItem = $this->getItemRepository()->findByUid(1);
        GeneralUtility::makeInstance(Tsfe::class)
            ->getTsfeByPageIdAndLanguageId(1);
        $document = $this->getDocumentFactory()->createDocumentForQueueItem($queueItem);

        self::assertEquals('http://testone.site/en/solrfals-page-context#c1', $document->fileReferenceUrl, 'Field "fileReferenceUrl" isn\'t filled with page id, as expected');
        self::assertEquals(2054827203, $document->endtime, 'Access field "endtime" isn\'t set correctly!');
        self::assertEquals('Hello EXT:solrfal', $document->fileReferenceTitle, 'Field "fileReferenceTitle" isn\'t filled with page title, as expected');
        self::assertEquals(1, $document->fieldDefinedViaGlobalConfiguration_intS, 'Field "fieldDefinedViaGlobalConfiguration_intS", defined via global configuration, isn\'t set correctly');
        self::assertEquals(1, $document->fieldDefinedViaPageContextConfiguration_intS, 'Field "fieldDefinedViaPageContextConfiguration_intS", defined via page context configuration, isn\'t set correctly');
        self::assertEquals('081dbd21084d40f28002304ab2b6b739200c52b9/tx_solr_file/9999', $document->variantId, 'Field "variantId" did not contain expected content');
    }

    /**
     * Tests the document creation of protected files page context
     *
     * @test
     * @dataProvider getProtectedFileHandlingVariants
     *
     * @param strint $additionalTypoScript
     * @param string $accessFile1
     * @param string $accessFile2
     * @noinspection PhpUndefinedFieldInspection
     */
    public function handleProtectedFilesInPageContext(string $additionalTypoScript, string $accessFile1, string $accessFile2): void
    {
        $this->importDataSetFromFixture('index_protected_file_in_page_context.xml');
        $this->addTypoScriptToTemplateRecord(1, $additionalTypoScript);

        $this->placeTemporaryFile('file9999.txt', 'fileadmin');
        $this->placeTemporaryFile('file8888.txt', 'fileadmin');


        GeneralUtility::makeInstance(Tsfe::class)
            ->getTsfeByPageIdAndLanguageId(2);

        $queueItem = $this->getItemRepository()->findByUid(1);
        $document = $this->getDocumentFactory()->createDocumentForQueueItem($queueItem);
        self::assertEquals(
            'http://testone.site/en/solrfals-page-context#c1',
            $document->fileReferenceUrl,
            'Field "fileReferenceUrl" isn\'t filled with page id, as expected'
        );
        self::assertEquals(
            9999,
            $document->fileUid,
            'Field "fileUid" isn\'t filled with file uid, as expected'
        );
        self::assertEquals(
            $accessFile1,
            $document->access,
            'Access field "fe_group" isn\'t set correctly!'
        );
        self::assertEquals(
            'Hello EXT:solrfal',
            $document->fileReferenceTitle,
            'Field "fileReferenceTitle" isn\'t filled with page title, as expected'
        );
        self::assertEquals(
            '081dbd21084d40f28002304ab2b6b739200c52b9/tx_solr_file/9999',
            $document->variantId,
            'Field "variantId" did not contain expected content'
        );

        $queueItem = $this->getItemRepository()->findByUid(2);
        $document = $this->getDocumentFactory()->createDocumentForQueueItem($queueItem);
        self::assertEquals(
            'http://testone.site/en/solrfals-page-context#c2',
            $document->fileReferenceUrl,
            'Field "fileReferenceUrl" isn\'t filled with page id, as expected'
        );
        self::assertEquals(
            8888,
            $document->fileUid,
            'Field "fileUid" isn\'t filled with file uid, as expected'
        );
        self::assertEquals(
            $accessFile2,
            $document->access,
            'Access field "fe_group" isn\'t set correctly!'
        );
        self::assertEquals(
            'Hello EXT:solrfal',
            $document->fileReferenceTitle,
            'Field "fileReferenceTitle" isn\'t filled with page title, as expected'
        );
        self::assertEquals(
            '081dbd21084d40f28002304ab2b6b739200c52b9/tx_solr_file/8888',
            $document->variantId,
            'Field "variantId" did not contain expected content'
        );
    }

    /**
     * Returns the group and TypoScript setting variants
     *
     * @return array
     */
    public function getProtectedFileHandlingVariants(): array
    {
        return [
            // handle protected files, considering page & ce access
            // "pageContext.enableFields.accessGroups" and "pageContext.contentEnableFields.accessGroups" configured
            [
                '',
                '2:1/c:0',
                '2:1/c:1,2',
            ],

            // handle protected files, ignoring page access settings
            // "pageContext.enableFields.accessGroups" not configured
            [
                /* @lang TYPO3_TypoScript */
                '
                plugin.tx_solr.index.enableFileIndexing.pageContext.enableFields.accessGroups >
                ',
                'c:0',
                'c:1,2',
            ],

            // handle protected files, ignoring content element access settings
            // "pageContext.contentEnableFields.accessGroups" not configured
            [
                /* @lang TYPO3_TypoScript */
                '
                plugin.tx_solr.index.enableFileIndexing.pageContext.contentEnableFields.accessGroups >
                ',
                '2:1/c:0',
                '2:1/c:0',
            ],

            // handle protected files, ignoring all access settings
            // "pageContext.enableFields.accessGroups" and "pageContext.contentEnableFields.accessGroups" not configured
            [
                /* @lang TYPO3_TypoScript */
                '
                plugin.tx_solr.index.enableFileIndexing.pageContext.enableFields.accessGroups >
                plugin.tx_solr.index.enableFileIndexing.pageContext.contentEnableFields.accessGroups >
                ',
                'c:0',
                'c:0',
            ],
        ];
    }

    /**
     * @return DocumentFactory
     */
    protected function getDocumentFactory(): DocumentFactory
    {
        return GeneralUtility::makeInstance(DocumentFactory::class);
    }
}
