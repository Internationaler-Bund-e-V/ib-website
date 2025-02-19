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

namespace ApacheSolrForTypo3\Solrfal\EventListener;

use ApacheSolrForTypo3\Solr\Event\Indexing\AfterItemHasBeenIndexedEvent;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @noinspection PhpUnused Used in {@link Configuration/Services.yaml} as listener.
 */
class IndexEventListener
{
    /**
     * Reset the successful file uids
     *
     * Successfully detected file uids were stored in registry
     * while indexing all variants of a page. After completing the
     * indexing of the item the uids must be cleared.
     *
     * Used in {@link Configuration/Services.yaml} as listener method.
     * @noinspection PhpUnused, PhpUnusedParameterInspection
     */
    public function resetSuccessfulFileUids(AfterItemHasBeenIndexedEvent $afterIndexItemEvent): void
    {
        $this->getRegistry()->remove('tx_solrfal', 'pageContextDetector.successfulFileUids');
    }

    /**
     * Returns a registry instance
     */
    protected function getRegistry(): Registry
    {
        return GeneralUtility::makeInstance(Registry::class);
    }
}
