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

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\QueueItemRepository;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Configurations;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\ItemTypes;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\ItemUids;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Uids;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The delete command is responsible to delete items from the index queue
 */
class SolrQueueDeleteCommand extends AbstractSolrCommand
{
    /**
     * @var Sites
     */
    protected $sitesHelper;

    /**
     * @var Configurations
     */
    protected $configurationsHelper;

    /**
     * @var QueueItemRepository
     */
    protected $queueItemRepository;

    /**
     * @var array
     */
    private $sites;

    /**
     * @var array
     */
    private $configurations;

    /**
     * @var array
     */
    private $types;

    /**
     * @var array
     */
    private $itemUids;

    /**
     * @var array
     */
    private $uids;

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma separated list of site root uids, if none is passed the queues for all sites get initialized', 0);
        $this->addOption('configurations', 'c', InputOption::VALUE_OPTIONAL, 'Comma separated list of index configurations to filter on during deletion.', '*');
        $this->addOption('item-types', 't', InputOption::VALUE_OPTIONAL, 'Comma separated list of item_type\'s (table of the record).', '*');
        $this->addOption('item-uids', 'i', InputOption::VALUE_OPTIONAL, 'Comma separated list of item_uid\'s (uid of the record).', '*');
        $this->addOption('uids', 'u', InputOption::VALUE_OPTIONAL, 'Comma separated list of uids\'s (uid of the queue item).', '*');

        $this->setDescription('Deletes items in the index queue');
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws DBALException|\Doctrine\DBAL\Exception
     */
    protected function loadOptions(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $this->sites = $this->getSitesHelper()->run($io, $input, $output);

        /** @var $configurationHelper Configurations */
        $configurationHelper = GeneralUtility::makeInstance(Configurations::class);
        $this->configurations = $configurationHelper->run($io, $input);

        /** @var $typesHelper ItemTypes */
        $typesHelper = GeneralUtility::makeInstance(ItemTypes::class);
        $this->types = $typesHelper->run($io, $input);

        /** @var $itemUidsHelper ItemUids */
        $itemUidsHelper = GeneralUtility::makeInstance(ItemUids::class);
        $this->itemUids = $itemUidsHelper->run($io, $input);

        /** @var $uidsHelper Uids */
        $uidsHelper = GeneralUtility::makeInstance(Uids::class);
        $this->uids = $uidsHelper->run($io, $input);

        $count = $this->getQueueItemRepository()->countItems($this->sites, $this->configurations, $this->types, $this->itemUids, $this->uids);
        $io->write("Items that will be deleted (Filters combined with AND): " . $count);
        $io->newLine(2);

        return $io->confirm('Is this correct?', true);
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $confirmed = $this->loadOptions($io, $input, $output);

        if(!$confirmed) {
            $io->write('Skipped');
            $io->newLine(1);
            return 1;
        }

        $this->getQueueItemRepository()->deleteItems($this->sites, $this->configurations, $this->types, $this->itemUids, $this->uids);

        $io->write('<fg=green>Done</>');
        $io->newLine(1);

        return 0;
    }

    /**
     * @return QueueItemRepository
     */
    public function getQueueItemRepository(): QueueItemRepository
    {
        $this->queueItemRepository = $this->queueItemRepository ?? GeneralUtility::makeInstance(QueueItemRepository::class);

        return $this->queueItemRepository;
    }

    /**
     * @param QueueItemRepository $queueItemRepository
     */
    public function setQueueItemRepository(QueueItemRepository $queueItemRepository)
    {
        $this->queueItemRepository = $queueItemRepository;
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
}
