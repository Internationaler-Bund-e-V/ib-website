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

use ApacheSolrForTypo3\Solrfal\Queue\ConsistencyAspect;
use Throwable;
use TYPO3\CMS\Core\Resource\Event\AfterFileUpdatedInIndexEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterFileUpdatedInIndex
{
    /**
     * @throws Throwable
     */
    public function __invoke(AfterFileUpdatedInIndexEvent $event): void
    {
        GeneralUtility::makeInstance(ConsistencyAspect::class)
            ->fileIndexRecordUpdated($event->getFile()->getUid());
    }
}
