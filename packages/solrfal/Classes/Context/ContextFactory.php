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

namespace ApacheSolrForTypo3\Solrfal\Context;

use ApacheSolrForTypo3\Solr\Access\Rootline;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solrfal\Detection\PageContextDetector;
use ApacheSolrForTypo3\Solrfal\Detection\RecordContextDetector;
use ApacheSolrForTypo3\Solrfal\Detection\RecordDetectionInterface;
use ApacheSolrForTypo3\Solrfal\Detection\StorageContextDetector;
use Doctrine\DBAL\Exception as DBALException;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContextFactory
 */
class ContextFactory implements ContextFactoryInterface
{
    protected SiteRepository $siteRepository;

    /**
     * ContextFactory constructor.
     */
    public function __construct(SiteRepository $siteRepository = null)
    {
        $this->siteRepository = $siteRepository ?? GeneralUtility::makeInstance(SiteRepository::class);
    }

    /**
     * @var array<string, array{class: class-string<object>, detection: class-string<object>, factory: class-string<object>|null}>
     */
    protected static array $typeMapping = [
        'page' => [
            'class'     => PageContext::class,
            'detection' => PageContextDetector::class,
            'factory'   => null,
        ],
        'storage' => [
            'class'     => StorageContext::class,
            'detection' => StorageContextDetector::class,
            'factory'   => null,
        ],
        'record' => [
            'class'     => RecordContext::class,
            'detection' => RecordContextDetector::class,
            'factory'   => null,
        ],
    ];

    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function getByRecord(array $row): ContextInterface
    {
        $type = $row['context_type'];
        if (!array_key_exists($type, self::$typeMapping)) {
            throw new RuntimeException('Unknown context type', 1382006080);
        }
        $className = self::$typeMapping[$type]['class'];
        $customFactory = self::$typeMapping[$type]['factory'];
        if ($customFactory === null) {
            $accessRootline = GeneralUtility::makeInstance(Rootline::class, $row['context_access_restrictions']);
            $site = $this->siteRepository->getSiteByRootPageId($row['context_site']);
            $language = (int)($row['context_language']);
            $additionalFields = json_decode($row['context_additional_fields'] ?? '', true, 2);
            if (!is_array($additionalFields)) {
                $additionalFields = [];
            }

            switch ($type) {
                case 'storage':
                    $indexingConfiguration =  $row['context_record_indexing_configuration'];
                    /** @var StorageContext $object */
                    $object = GeneralUtility::makeInstance(
                        $className,
                        $site,
                        $accessRootline,
                        0,
                        0,
                        $indexingConfiguration,
                        $language,
                    );
                    break;
                case 'record':
                    $table = $row['context_record_table'];
                    $field = $row['context_record_field'];
                    $uid = (int)($row['context_record_uid']);
                    $pid = (int)($row['context_record_pid']);
                    $indexingConfiguration =  $row['context_record_indexing_configuration'];
                    /** @var RecordContext $object */
                    $object = GeneralUtility::makeInstance(
                        $className,
                        $site,
                        $accessRootline,
                        $table,
                        $field,
                        $uid,
                        $pid,
                        $indexingConfiguration,
                        $language,
                    );
                    break;
                case 'page':
                    $table = $row['context_record_table'];
                    $field = $row['context_record_field'];
                    $uid = (int)($row['context_record_uid']);
                    $pid = (int)($row['context_record_pid']);
                    /** @var PageContext $object */
                    $object = GeneralUtility::makeInstance(
                        $className,
                        $site,
                        $accessRootline,
                        $table,
                        $field,
                        $uid,
                        $pid,
                        $language,
                    );
                    break;
                default:
                    throw new RuntimeException('You registered a custom Context without providing a Factory', 1382006090);
            }
            $object->setAdditionalDocumentFields($additionalFields);
            return $object;
        }
        /** @var ContextFactoryInterface $factory */
        $factory = GeneralUtility::makeInstance($customFactory);
        return $factory->getByRecord($row);
    }

    /**
     * Allows registering custom Context-Types for Indexing,
     * or replacing the original implementations
     *
     * @throws RuntimeException
     */
    public static function registerType(
        string $typeName,
        string $implementationClass,
        string $detectionClass,
        ?string $customFactory = null
    ): void {
        if (!is_subclass_of($implementationClass, ContextInterface::class)) {
            throw new RuntimeException('Custom Indexing contexts need to implement the ContextInterface', 1382006059);
        }
        if (!is_subclass_of($detectionClass, RecordDetectionInterface::class)) {
            throw new RuntimeException('The detector of a custom indexing context needs to implement the ContextInterface', 1382006071);
        }
        if ($customFactory !== null && !is_subclass_of($customFactory, ContextFactoryInterface::class)) {
            throw new RuntimeException('A custom ContextFactory needs to implement the ContextFactoryInterface', 1382006070);
        }
        self::$typeMapping[$typeName] = [
            'class'     => $implementationClass,
            'factory'   => $customFactory,
            'detection' => $detectionClass,
        ];
    }

    /**
     * @return RecordDetectionInterface[]
     */
    public static function getContextDetectors(Site $site): array
    {
        $detectors = [];
        // Fetch site configuration once and use this for all detectors in loop
        $siteConfiguration = $site->getSolrConfiguration();
        /** @var array{detection: class-string<object>} $configuration */
        foreach (self::$typeMapping as $configuration) {
            /** @var RecordDetectionInterface $detectorInstance */
            $detectorInstance = GeneralUtility::makeInstance($configuration['detection'], $site, $siteConfiguration);
            $detectors[] = $detectorInstance;
        }
        return $detectors;
    }
}
