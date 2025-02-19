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

use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Configurations;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\ContextNames;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\ItemUids;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Languages;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Uids;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The delete command is responsible to delete items from the file index queue
 */
class SolrfalQueueDeleteCommand extends AbstractSolrfalCommand
{
    /**
     * @var Sites
     */
    protected Sites $sitesHelper;

    /**
     * @var ItemRepository
     */
    protected ItemRepository $itemRepository;

    /**
     * @var array
     */
    private array $sites;

    /**
     * @var array
     */
    private array $configurations;

    /**
     * @var array
     */
    private array $contextNames;

    /**
     * @var array
     */
    private array $itemUids;

    /**
     * @var array
     */
    private array $languageUids;

    /**
     * @var array
     */
    private array $uids;

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma separated list of site root uids, if none is passed the queues for all sites get initialized', 0);
        $this->addOption('configurations', 'c', InputOption::VALUE_OPTIONAL, 'Comma separated list of index configurations to filter on during deletion.', '*');
        $this->addOption('context-names', 'cn', InputOption::VALUE_OPTIONAL, 'Comma separated list of context names\'s (e.g. storage, record, page).', '*');
        $this->addOption('item-uids', 'i', InputOption::VALUE_OPTIONAL, 'Comma separated list of item_uid\'s (uid of the record).', '*');
        $this->addOption('languages', 'l', InputOption::VALUE_OPTIONAL, 'Comma separated list of language uid\'s.', '*');
        $this->addOption('uids', 'u', InputOption::VALUE_OPTIONAL, 'Comma separated list of uids\'s (uid of the queue item).', '*');

        $this->setDescription('Deletes items in the file index queue');
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws DBALDriverException
     * @throws DBALException
     */
    protected function loadOptions(SymfonyStyle $io, InputInterface $input, OutputInterface $output): bool
    {
        $this->sites = $this->getSitesHelper()->run($io, $input, $output);

        /** @var Configurations $configurationHelper */
        $configurationHelper = GeneralUtility::makeInstance(Configurations::class);
        $this->configurations = $configurationHelper->run($io, $input);

        /** @var ContextNames $contextNamesHelper */
        $contextNamesHelper = GeneralUtility::makeInstance(ContextNames::class);
        $this->contextNames = $contextNamesHelper->run($io, $input);

        /** @var ItemUids $itemUidsHelper */
        $itemUidsHelper = GeneralUtility::makeInstance(ItemUids::class);
        $this->itemUids = $itemUidsHelper->run($io, $input);

        /** @var Languages $languagesUidHelper */
        $languagesUidHelper = GeneralUtility::makeInstance(Languages::class);
        $this->languageUids = $languagesUidHelper->run($io, $input);

        /** @var Uids $uidsHelper */
        $uidsHelper = GeneralUtility::makeInstance(Uids::class);
        $this->uids = $uidsHelper->run($io, $input);

        $count = $this->getItemRepository()->countBy($this->sites, $this->configurations, $this->contextNames, $this->itemUids, $this->uids, $this->languageUids);
        $io->write('Items that will be deleted (Filters combined with AND): ' . $count);
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

        // @todo we need to think about if we want to allow a deletion in solr on queue deletion as well, by now consitent with solr:queue:delete command => no deletion from solr
        $deleteInSolr = false;
        $this->getItemRepository()->removeBy($this->sites, $this->configurations, $this->contextNames, $this->itemUids, $this->uids, $this->languageUids, $deleteInSolr);

        $io->write('<fg=green>Done</>');
        $io->newLine(1);

        return self::SUCCESS;
    }

    /**
     * @return ItemRepository
     */
    public function getItemRepository(): ItemRepository
    {
        $this->itemRepository = $this->itemRepository ?? GeneralUtility::makeInstance(ItemRepository::class);

        return $this->itemRepository;
    }

    /**
     * @param ItemRepository $queueItemRepository
     */
    public function setItemRepository(ItemRepository $queueItemRepository)
    {
        $this->itemRepository = $queueItemRepository;
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
}
