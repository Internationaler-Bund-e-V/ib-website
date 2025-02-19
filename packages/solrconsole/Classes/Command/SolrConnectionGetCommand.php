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

namespace ApacheSolrForTypo3\Solrconsole\Command;

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The connections command is responsible to get all connections by site.
 */
class SolrConnectionGetCommand extends AbstractSolrCommand
{
    protected SiteRepository $siteRepository;
    protected ConnectionManager $connectionManager;

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->setDescription('Shows the solr connections by site in the installation');
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $sites = $this->getSiteRepository()->getAvailableSites();

        foreach ($sites as $site) {
            $connections = $this->getConnectionManager()->getConnectionsBySite($site);
            $io->writeln('Site: ' . $site->getLabel());
            $io->writeln('Connections ' . count($connections) . ':');

            foreach ($connections as $connection) {
                $readConnectionString = $connection->getEndpoint('read')->getCoreBaseUri();
                $writeConnectionString = $connection->getEndpoint('write')->getCoreBaseUri();

                $io->writeln('  * read connection: ' . $readConnectionString . ' / write connection: ' . $writeConnectionString);
                $io->newLine(1);
            }

            $io->writeln('#################################');
        }

        $io->write('<fg=green>Done</>');
        $io->newLine(1);

        return self::SUCCESS;
    }

    /**
     * @return SiteRepository
     */
    public function getSiteRepository(): SiteRepository
    {
        $this->siteRepository = $this->siteRepository ?? GeneralUtility::makeInstance(SiteRepository::class);
        return $this->siteRepository;
    }

    /**
     * @param SiteRepository $siteRepository
     */
    public function setSiteRepository(SiteRepository $siteRepository)
    {
        $this->siteRepository = $siteRepository;
    }

    /**
     * @return ConnectionManager
     */
    public function getConnectionManager(): ConnectionManager
    {
        $this->connectionManager = $this->connectionManager ?? GeneralUtility::makeInstance(ConnectionManager::class);
        return $this->connectionManager;
    }

    /**
     * @param ConnectionManager $connectionManager
     */
    public function setConnectionManager(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }
}
