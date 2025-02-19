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

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\Statistic\QueueStatistic;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The initialize command is responsible to initialize the index queue for a set of sites and index queue configurations.
 */
class SolrQueueProgressCommand extends AbstractSolrCommand
{
    /**
     * @var ?Sites
     */
    protected ?Sites $sitesHelper = null;

    /**
     * @var ?Queue
     */
    protected ?Queue $indexQueue = null;

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma seperated list of site root uids, if none is passed the queues for all sites get initialized', 0);
        $this->setDescription('Shows the progress of index queue for a set of sites');
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws DBALDriverException
     * @throws DBALException
     * @throws Throwable
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($output->isVerbose()) {
            $io->title($this->getDescription());
            $io->newLine();
        }

        $sites = $this->getSitesHelper()->run($io, $input, $output);
        foreach ($sites as $i => $site) {
            /* @var Site $site*/
            $stats = $this->getStatisticsBySite($site);

            $io->writeln(
                'Site: ' . $site->getRootPageId(),
                OutputInterface::VERBOSITY_VERBOSE
            );
            $io->writeln(
                'Domain: ' . $site->getDomain(),
                OutputInterface::VERBOSITY_VERBOSE
            );
            $io->writeln(
                'Indexed: ' . $stats->getSuccessCount(),
                OutputInterface::VERBOSITY_VERBOSE
            );
            $io->writeln(
                'Pending: ' . $stats->getPendingCount(),
                OutputInterface::VERBOSITY_VERBOSE
            );
            $io->writeln(
                'Errors: ' . $stats->getFailedCount(),
                OutputInterface::VERBOSITY_VERBOSE
            );

            if (!$output->isVerbose()) {
                $io->writeln(
                    vsprintf(
                        'Site %s (%s) <fg=red>ERRORS:%s</>',
                        [
                            $site->getRootPageId(),
                            $site->getDomain(),
                            $stats->getFailedCount(),
                        ]
                    )
                );
            }
            $progressBar = $this->getProgressBar($io, $stats->getSuccessCount() + $stats->getPendingCount() + $stats->getFailedCount());

            $progressBar->setBarCharacter('<fg=green>⚬</>');
            $progressBar->setEmptyBarCharacter('<fg=red>⚬</>');
            $progressBar->setProgressCharacter('<fg=green>➤</>');

            $progressBar->setProgress((int)round($stats->getSuccessCount()));
            if (count($sites) > $i) {
                $io->newLine(2);
            } else {
                $io->newLine();
            }
        }

        return self::SUCCESS;
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
    public function setSitesHelper(Sites $sitesHelper)
    {
        $this->sitesHelper = $sitesHelper;
    }

    /**
     * @return Queue
     */
    public function getIndexQueue(): Queue
    {
        if (!isset($this->indexQueue)) {
            $this->indexQueue = $this->indexQueue ?? GeneralUtility::makeInstance(Queue::class);
        }
        return $this->indexQueue;
    }

    /**
     * @param SymfonyStyle $io
     * @param int $steps
     * @return ProgressBar
     */
    public function getProgressBar(SymfonyStyle $io, int $steps): ProgressBar
    {
        return GeneralUtility::makeInstance(ProgressBar::class, $io, $steps);
    }

    /**
     * @param Queue $indexQueue
     */
    public function setIndexQueue(Queue $indexQueue): void
    {
        $this->indexQueue = $indexQueue;
    }

    /**
     * Returns the statistics object for given site.
     *
     * @param Site $site
     * @return QueueStatistic
     * @throws DBALDriverException
     * @throws DBALException
     */
    protected function getStatisticsBySite(Site $site): QueueStatistic
    {
        return $this->getIndexQueue()->getStatisticsBySite($site);
    }
}
