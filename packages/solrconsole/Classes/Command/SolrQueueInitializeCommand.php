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

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\QueueInitializationService;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Configurations;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The initialize command is responsible to initialize the index queue for a set of sites and index queue configurations.
 */
class SolrQueueInitializeCommand extends AbstractSolrCommand
{
    /**
     * @var QueueInitializationService
     */
    protected $queueInitializationService;

    /**
     * @var Sites
     */
    protected $sitesHelper;

    /**
     * @var Configurations
     */
    protected $configurationsHelper;

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
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma seperated list of site root zuds, if none is passed the queues for all sites get intialized', 0);
        $this->addOption('configurations', 'c', InputOption::VALUE_OPTIONAL, 'Comma seperated list of index configurations to initialize, if none is passed all index-configurations get initialize', '*');
        $this->setDescription('Initializes the index queue by adding new records to it');
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $sites = $this->getSitesHelper()->run($io, $input, $output);

        $configurationHelper = GeneralUtility::makeInstance(Configurations::class);
        $configurations = $configurationHelper->run($io, $input);

        $confirmed = $io->confirm('Is this correct?', true);
        if(!$confirmed) {
            $io->write('Skipped');
            $io->newLine(1);
            return 1;
        }

        $initializationResults = $this->doInitialization($sites, $configurations);
        $this->renderInitializationResults($io, $initializationResults);

        $io->write('<fg=green>Done</>');
        $io->newLine(1);

        return 0;
    }

    /**
     * Uses the QueueInitializationService to initialize a combination of sites and configurations.
     *
     * @param array $sites
     * @param array $configurations
     * @return array
     */
    protected function doInitialization($sites, $configurations): array
    {
        $configurations = $configurations === [] ? ['*'] : $configurations;
        $initializationResults = $this->getQueueInitializationService()->initializeBySitesAndConfigurations($sites, $configurations);
        return $initializationResults;
    }

    /**
     * Renders the initialization results.
     *
     * @param SymfonyStyle $io
     * @param $initializationResults
     */
    protected function renderInitializationResults($io, $initializationResults)
    {
        foreach ($initializationResults as $siteKey => $initializationResult) {
            $io->writeln('Initialized the following configurations for site ' . $siteKey . ': ');
            foreach ($initializationResult as $configurationName => $state) {
                $stageMessage = $state ? '<fg=green>ok</>' : '<fg=red>error</>';
                $io->writeln('Configuration: ' . $configurationName . ' ' . $stageMessage);
            }

            $io->newLine(1);
        }
    }

    /**
     * @return QueueInitializationService
     */
    public function getQueueInitializationService(): QueueInitializationService
    {
        if (is_null($this->queueInitializationService)) {
            /** @var $queue Queue */
            $queue = GeneralUtility::makeInstance(Queue::class);
            $this->queueInitializationService = GeneralUtility::makeInstance(QueueInitializationService::class, $queue);
        }
        return $this->queueInitializationService;
    }

    /**
     * @param QueueInitializationService $queueInitializationService
     */
    public function setQueueInitializationService(QueueInitializationService $queueInitializationService)
    {
        $this->queueInitializationService = $queueInitializationService;
    }
}
