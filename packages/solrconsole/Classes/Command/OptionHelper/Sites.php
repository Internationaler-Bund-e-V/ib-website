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

use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Sites
{
    /**
     * @var ?SiteRepository
     */
    private ?SiteRepository $siteRepository = null;

    /**
     * @param SiteRepository $siteRepository
     */
    public function setSiteRepository(SiteRepository $siteRepository)
    {
        $this->siteRepository = $siteRepository;
    }

    /**
     * @return SiteRepository
     */
    public function getSiteRepository(): SiteRepository
    {
        if (is_null($this->siteRepository)) {
            $this->siteRepository = GeneralUtility::makeInstance(SiteRepository::class);
        }

        return $this->siteRepository;
    }

    /**
     * Retrieves an array of Site objects from the passed --sites option. When nothing was passed, all sites will
     * be retrieved. When a comma separated list was passed, for each item in the list a Site object will be retrieved
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws DBALDriverException
     */
    protected function getSelectedSites(InputInterface $input, OutputInterface $output): array
    {
        $siteOptions = (string)$input->getOption('sites');
        $siteRepository = $this->getSiteRepository();

        if((int)$siteOptions === 0) {
            return $siteRepository->getAvailableSites();
        }

        $sites = [];
        $siteUids = GeneralUtility::trimExplode(',', $siteOptions);
        foreach($siteUids as $siteUid) {
            $solrConfiguredSite = $siteRepository->getSiteByRootPageId((int)$siteUid);
            if ($solrConfiguredSite === null) {
                $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
                $errOutput->writeln(
                    sprintf(
                        'Error: Requested site with uid "%s" is not fully configured for EXT:solr',
                        $siteUid
                    )
                );
                exit(1);
            }
            $sites[] = $solrConfiguredSite;
        }

        return $sites;
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param array $sites
     */
    private function renderSelectedSites(SymfonyStyle $io, InputInterface $input, array $sites)
    {
        $siteOption = (string)$input->getOption('sites');
        $listString = $siteOption === '0' ? 'all' : $siteOption;

        $io->writeln(
            'Sites ('.$listString.'):',
            OutputInterface::VERBOSITY_VERBOSE
        );
        foreach ($sites as $site) {
            /* @var Site $site  */
            $io->writeln(
                ' * ' . $site->getLabel(),
                OutputInterface::VERBOSITY_VERBOSE
            );
        }

        if ($io->isVerbose()) {
            $io->newLine();
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws DBALDriverException
     */
    public function run(SymfonyStyle $io, InputInterface $input, OutputInterface $output): array
    {
        $sites = $this->getSelectedSites($input, $output);
        $this->renderSelectedSites($io, $input, $sites);
        return $sites;
    }

}
