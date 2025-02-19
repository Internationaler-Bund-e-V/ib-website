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

use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SolrQueueResetErrorsCommand extends AbstractSolrCommand
{
    /**
     * @var Queue
     */
    protected Queue $indexQueue;

    /**
     * @var array
     */
    private array $sites;

    /**
     * @var Sites
     */
    protected Sites $sitesHelper;

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma seperated list of site root uids, if none is passed the errors for all sites will be resetted', 0);
        $this->setDescription('Resets the items in the index queue that are marked as errored and allows to re-index them');
    }

    /**
     * @return Sites
     */
    public function getSitesHelper(): Sites
    {
        $this->sitesHelper = $this->sitesHelper ?? GeneralUtility::makeInstance(Sites::class);
        return $this->sitesHelper;
    }

    /**
     * @param Sites $sitesHelper
     */
    public function setSitesHelper(Sites $sitesHelper): void
    {
        $this->sitesHelper = $sitesHelper;
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws DBALDriverException
     */
    protected function loadOptions(SymfonyStyle $io, InputInterface $input, OutputInterface $output): bool
    {
        $this->sites = $this->getSitesHelper()->run($io, $input, $output);

        return $io->confirm('Is this correct?', true);
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
        $confirmed = $this->loadOptions($io, $input, $output);

        if (!$confirmed) {
            $io->write('Skipped');
            $io->newLine(1);
            return self::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        foreach ($this->sites as $site) {
            $result = $this->getIndexQueue()->resetErrorsBySite($site);
            $io->writeln('Resetting errored items for site ' . $site->getDomain() . ': ' . $result . ' items have been reset');
        }

        $io->write('<fg=green>Done</>');
        $io->newLine(1);

        return self::SUCCESS;
    }

    /**
     * @return Queue
     */
    public function getIndexQueue(): Queue
    {
        $this->indexQueue = $this->indexQueue ?? GeneralUtility::makeInstance(Queue::class);
        return $this->indexQueue;
    }

    /**
     * @param Queue $indexQueue
     */
    public function setIndexQueue(Queue $indexQueue)
    {
        $this->indexQueue = $indexQueue;
    }
}
