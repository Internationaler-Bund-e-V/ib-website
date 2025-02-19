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

namespace ApacheSolrForTypo3\Solrconsole\Command\OptionHelper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The rootPagesIds helper is used when something needs to be done in the context of
 * a site, but no site object is required. This is for example the case when the sites get initialized.
 *
 * Class RootPageIds
 */
class SiteRootPageIds
{
    /**
     * Retrieves an array of rootPageIds
     * @param InputInterface $input
     * @return array
     */
    protected function getSiteRootPageIds(InputInterface $input): array
    {
        $siteOptions = (string)$input->getOption('sites');
        $sites = [];
        $siteUids = GeneralUtility::trimExplode(',', $siteOptions);
        foreach ($siteUids as $siteUid) {
            $sites[] = (int)$siteUid;
        }

        return $sites;
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     */
    private function renderSelectedSiteRootPageIds(SymfonyStyle $io, InputInterface $input): void
    {
        $siteOption = (string)$input->getOption('sites');
        $listString = $siteOption === '0' ? 'all' : $siteOption;
        $io->writeln('Sites (' . $listString . '):');
        $io->newLine(1);
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return array
     */
    public function run(SymfonyStyle $io, InputInterface $input): array
    {
        $rootPageIds = $this->getSiteRootPageIds($input);
        $this->renderSelectedSiteRootPageIds($io, $input);
        return $rootPageIds;
    }
}
