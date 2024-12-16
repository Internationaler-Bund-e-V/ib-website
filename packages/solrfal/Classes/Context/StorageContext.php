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

use ApacheSolrForTypo3\Solr\FrontendEnvironment;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StorageContext
 */
class StorageContext extends AbstractContext
{

    /**
     * @return string
     */
    public function getContextIdentifier(): string
    {
        return 'storage';
    }

    public function getAdditionalDynamicDocumentFields(File $file): array
    {
        $fields = [];
        /* @var TypoScriptConfiguration $storageConfiguration */
        $storageConfiguration = GeneralUtility::makeInstance(FrontendEnvironment::class)->getSolrConfigurationFromPageId(
            $this->getSite()->getRootPageId(),
            $this->getLanguage()
        );
        $enableFields = $storageConfiguration->getObjectByPathOrDefault('plugin.tx_solr.index.enableFileIndexing.storageContext.' . $this->getIdentifierForItemSpecificFieldConfiguration() . '.enableFields.', []);
        foreach ($enableFields as $identifier => $fieldName) {
            switch ($identifier) {
                case 'endtime':
                    if ((int)$file->getProperty($fieldName) !== 0) {
                        $fields['endtime'] = (int)$file->getProperty($fieldName);
                    }
                    break;
                case 'accessGroups':
                    if (!empty($file->getProperty($fieldName))) {
                        $fields['access'] = 'r:' . trim($file->getProperty($fieldName));
                    } else {
                        $fields['access'] = 'r:0';
                    }
                    break;
                default:
            }
        }
        return $fields;
    }

    /**
     * Returns an identifier, which will be used for looking up special
     * configurations in TypoScript like storage uid in storageContext
     * or table name in recordContext
     *
     * @return string
     */
    protected function getIdentifierForItemSpecificFieldConfiguration(): string
    {
        return (string)$this->additionalDocumentFields['fileStorage'];
    }
}
