<?php

declare(strict_types=1);

/**
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

namespace ApacheSolrForTypo3\Solrfal\System\Environment;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FrontendHttpHost
 */
class FrontendServerEnvironment implements SingletonInterface
{
    /**
     * Contains copy/backup of $_SERVER variable.
     *
     * @var array<string, mixed>
     */
    protected array $originalServerVariables = [];

    /**
     * @param int $rootPageId
     * @param string|null $scriptFileName
     * @param string $phpSelf
     * @param string $scriptName
     *
     * @return bool
     *
     * @throws SiteNotFoundException
     */
    public function initializeByRootPageId(
        int $rootPageId,
        ?string $scriptFileName = null,
        string $phpSelf = '/index.php',
        string $scriptName = '/index.php'
    ): bool {
        $scriptFileName = $scriptFileName ?? Environment::getPublicPath();
        static $hosts = [];

        $hostFound = !empty($hosts[$rootPageId]);
        if ($hostFound) {
            $_SERVER['HTTP_HOST'] = $hosts[$rootPageId];
            $_SERVER['SCRIPT_FILENAME'] = $scriptFileName;
            $_SERVER['PHP_SELF'] = $phpSelf;
            $_SERVER['SCRIPT_NAME'] = $scriptName;
            return true;
        }

        $host = $this->getHostByPageId($rootPageId);

        $hosts[$rootPageId] = $host;
        $hostFound = !empty($hosts[$rootPageId]);

        if ($hostFound) {
            $this->originalServerVariables = $_SERVER;
            $_SERVER['HTTP_HOST'] = $hosts[$rootPageId];
            $_SERVER['SCRIPT_FILENAME'] = $scriptFileName;
            $_SERVER['PHP_SELF'] = $phpSelf;
            $_SERVER['SCRIPT_NAME'] = $scriptName;
            return true;
        }

        return false;
    }

    public function restore(): void
    {
        $_SERVER = $this->originalServerVariables;
    }

    /**
     * Returns host component from configured base in site configuration.
     *
     * @param int $pageId PID to use
     * @param array<string, mixed>|null $rootLine
     * @param string|null $mountPointParameter
     *
     * @return string
     *
     * @throws SiteNotFoundException
     */
    protected function getHostByPageId(int $pageId, array $rootLine = null, string $mountPointParameter = null): string
    {
        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($pageId, $rootLine, $mountPointParameter);
        return $site->getBase()->getHost();
    }
}
