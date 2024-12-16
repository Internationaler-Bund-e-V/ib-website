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

use stdClass;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class encapsulates the access to the extension configuration.
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class OverlayService
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @return PageRepository
     */
    protected function getInitializedPageRepository(): PageRepository
    {
        if ($this->pageRepository !== null) {
            return $this->pageRepository;
        }

        /* @var PageRepository $page */ // @todo: Check proper instantiation
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        return $this->pageRepository;
    }

    /**
     * Returns an overlaid version of a record.
     *
     * @param string $tableName
     * @param array $record
     * @param int $languageUid
     * @param string $overlayMode
     * @return mixed
     * @throws AspectNotFoundException
     * @todo: TYPO3 11.5
     */
    public function getRecordOverlay(string $tableName, array $record, int $languageUid, string $overlayMode = '')
    {
        /**
         * @todo This is required for the frontend restriction container,
         * when used in the backend for TYPO3 9 the overlaying should be done with an own method
         *
         * @todo: Since EXT:solr 11.5 the $GLOBALS['TSFE'] MUST NOT be used anymore
         */
        if (!isset($GLOBALS['TSFE'])) {
            $GLOBALS['TSFE'] = new stdClass();
        }
        if (null === GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'groupIds')) {
            $GLOBALS['TSFE']->gr_list = '';
        }

        return $this->getInitializedPageRepository()->getRecordOverlay($tableName, $record, (int)$languageUid, $overlayMode);
    }
}
