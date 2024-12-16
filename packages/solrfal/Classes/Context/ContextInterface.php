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
use TYPO3\CMS\Core\Resource\File;

/**
 * Class Context
 */
interface ContextInterface
{
    /**
     * @return string
     */
    public function getContextIdentifier(): string;

    /**
     * Returns the Site
     *
     * @return Site
     */
    public function getSite(): Site;

    /**
     * @return int
     */
    public function getLanguage(): int;

    /**
     * @return Rootline
     */
    public function getAccessRestrictions(): Rootline;

    /**
     * Returns the pageId of this context
     *
     * @return int
     */
    public function getPageId(): int;

    /**
     * Returns an array of context specific field to add to the solr document
     *
     * @return array
     */
    public function getAdditionalStaticDocumentFields(): array;

    /**
     * Returns an array of context specific field to add to the solr document,
     * dynamically calculated from the FILE
     *
     * @param File $file
     * @return array
     */
    public function getAdditionalDynamicDocumentFields(File $file): array;

    /**
     * @return array
     */
    public function getSpecificFieldConfigurationTypoScript(): array;

    /**
     * Returns the array representation for database storage
     *
     * @return array
     */
    public function toArray(): array;
}
