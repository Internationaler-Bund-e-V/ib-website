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

use ApacheSolrForTypo3\Solr\Domain\Index\IndexService;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The process command is used to process an amount of itmes from the index queue
 */
class SolrQueueIndexCommand extends AbstractSolrCommand
{
    /**
     * @var Sites
     */
    protected Sites $sitesHelper;

    /**
     * @var Queue
     */
    protected Queue $indexQueue;

    /**
     * @var Site[]
     */
    private array $sites;

    /**
     * @var int
     */
    private int $amount;

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma seperated list of site root uids, if none is passed the queues for all sites get processed', 0);
        $this->addOption('amount', 'a', InputOption::VALUE_OPTIONAL, 'Number of items that should be index in one run', 10);

        $this->setDescription('Processes the index queue and index the items into the solr index.');
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function loadOptions(SymfonyStyle $io, InputInterface $input, OutputInterface $output): bool
    {
        $this->sites = $this->getSitesHelper()->run($io, $input, $output);

        $amount = $input->getOption('amount');
        $this->amount = (int)($amount ?? 10);
        $io->writeln('Amount of items to index: ' . $this->amount);
        return $io->confirm('Is this correct?', true);
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
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

        foreach ($this->sites as $site) {
            /** @var IndexService $indexService */
            $indexService = GeneralUtility::makeInstance(IndexService::class, $site);
            $indexWithoutErrors = $indexService->indexItems($this->amount);
            $state = $indexWithoutErrors ? '<fg=green>Ok</>' : '<fg=red>Errored</>';
            $io->writeln('Indexed ' . $this->amount . ' items for site ' . $site->getDomain() . ': ' . $state);
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
    public function setSitesHelper(Sites $sitesHelper): void
    {
        $this->sitesHelper = $sitesHelper;
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
    public function setIndexQueue(Queue $indexQueue): void
    {
        $this->indexQueue = $indexQueue;
    }
}
