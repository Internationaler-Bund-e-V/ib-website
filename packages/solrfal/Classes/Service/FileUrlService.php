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

namespace ApacheSolrForTypo3\Solrfal\Service;

use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\CMS\Core\Routing\UrlGenerator;
use TYPO3\CMS\Core\Site\Entity\Site as Typo3Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileUrlService
 */
class FileUrlService
{
    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Returns the public url for given file
     *
     * @param File $file
     * @param Typo3Site $site
     * @return string
     */
    public function getPublicUrl(
        File $file,
        Typo3Site $site,
        int $languageId
    ): string {
        if ($file->getStorage()->isPublic()) {
            return $file->getPublicUrl();
        }
        $language = $site->getLanguageById($languageId);
        $collection = new RouteCollection();
        $defaultRoute = new Route(
            '/index.php',
            [],
            [],
            ['utf8' => true]
        );
        $collection->add('default', $defaultRoute);

        $scheme = $language->getBase()->getScheme();
        $requestContext = new RequestContext(
            // page segment (slug & enhanced part) is supposed to start with '/'
            rtrim($language->getBase()->getPath(), '/'),
            'GET',
            $language->getBase()->getHost(),
            $scheme ?: 'http',
            $scheme === 'http' ? $language->getBase()->getPort() ?? 80 : 80,
            $scheme === 'https' ? $language->getBase()->getPort() ?? 443 : 443
        );

        $generator = new UrlGenerator($collection, $requestContext);
        return $generator->generate(
            'default',
            $this->getDumpFileParameters($file),
            UrlGenerator::ABSOLUTE_URL
        );
    }

    /**
     * Returns the required parameters for "dumpFile"
     *
     * @param File $file
     * @return array
     */
    protected function getDumpFileParameters(File $file): array
    {
        $params = [
            'eID' => 'dumpFile',
            't' => 'f',
            'f' => $file->getUid(),
        ];
        $params['token'] = GeneralUtility::hmac(implode('|', $params), 'resourceStorageDumpFile');

        return $params;
    }
}
