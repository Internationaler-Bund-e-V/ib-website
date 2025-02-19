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
    public function getContextIdentifier(): string;

    public function getSite(): Site;

    public function getLanguage(): int;

    public function getAccessRestrictions(): Rootline;

    /**
     * Returns the uid of context's record
     */
    public function getUid(): int;

    /**
     * Returns the pid of context's record.
     */
    public function getPid(): int;

    /**
     * Returns
     * Used to simulate the TSFE/LanguageAspect to use for translations-resolvers.
     */
    public function getPidForCoreContext(): int;

    /**
     * Returns an array of context specific field to add to the solr document
     *
     * @return array<string, string|int|bool>
     */
    public function getAdditionalStaticDocumentFields(): array;

    /**
     * Returns an array of context specific field to add to the solr document,
     * dynamically calculated from the FILE
     *
     * @return array<string, int|string>
     */
    public function getAdditionalDynamicDocumentFields(File $file): array;

    /**
     * Resolves the field-processing TypoScript configuration which is specific
     * to the current context.
     * Will be merged in the default field-processing configuration and takes
     * precedence over the default configuration.
     *
     * @return array<string, mixed>
     */
    public function getSpecificFieldConfigurationTypoScript(): array;

    /**
     * Returns the array representation for database storage
     *
     * @return array<string, int|string|bool>
     */
    public function toArray(): array;
}
