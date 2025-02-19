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

namespace ApacheSolrForTypo3\Solrfal\System\Language;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is responsible for fetching the overlay/translation records for desired language.
 */
class OverlayService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Context $coreContext
    ) {}

    /**
     * @var PageRepository[]
     */
    protected array $pageRepositories = [];

    /**
     * Returns an overlaid version of a record.
     *
     * @param string $tableName The table name to extract file references from.
     * @param array{uid: int, pid: int} $record
     * @param int $languageUid
     *
     * @return array{uid: int, pid: int}|null The translation if found
     */
    public function getRecordOverlay(
        string $tableName,
        array $record,
        int $languageUid,
    ): ?array {
        /** @var array{uid: int, pid: int}|null $overlay */
        $overlay = $this->getInitializedPageRepository((int)($record['pid'] ?? 0), $languageUid)
            ->getLanguageOverlay(
                $tableName,
                $record
            );

        return $overlay;
    }

    protected function getInitializedPageRepository(
        int $pageUid,
        int $languageUid,
    ): ?PageRepository {
        $pageRepositoryIdentifier = 'pageUid:' . $pageUid . '|' . $languageUid;
        if (array_key_exists($pageRepositoryIdentifier, $this->pageRepositories)) {
            return $this->pageRepositories[$pageRepositoryIdentifier];
        }

        return $this->pageRepositories[$pageRepositoryIdentifier] = GeneralUtility::makeInstance(
            PageRepository::class,
            $this->coreContext
        );
    }
}
