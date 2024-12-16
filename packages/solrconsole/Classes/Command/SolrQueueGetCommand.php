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
use ApacheSolrForTypo3\Solr\IndexQueue\Item;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Configurations;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\ItemTypes;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\ItemUids;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Uids;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The delete command is responsible to delete items from the index queue
 */
class SolrQueueGetCommand extends AbstractSolrCommand
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
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $perPage;

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
        $this->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Page of items that should be shown.', 1);
        $this->addOption('per-page', null, InputOption::VALUE_OPTIONAL, 'Items count per page.', 10);

        $this->setDescription('Shows items that are currently in the index queue');
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
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

        $page = $input->getOption('page');
        $this->page = (int)($page ?? 1);
        $io->writeln('Page: ' . $this->page);
        $io->newLine(1);

        $perPage = $input->getOption('per-page');
        $this->perPage = (int)($perPage ?? 10);
        $io->writeln('Item count per page: ' . $this->perPage);
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

        $start = ($this->page - 1) * $this->perPage;
        $items = $this->getQueueItemRepository()->findItems($this->sites, $this->configurations, $this->types, $this->itemUids, $this->uids, $start, $this->perPage);

        foreach ($items as $item) {
            $io->writeln('#################################');
            $io->newLine(1);
            $this->renderQueueItem($io, $item);
            $io->newLine(1);
        }

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

    /**
     * @param Item $item
     * @return string
     */
    protected function getStateMessage($item):string
    {
        $state = $item->getState();
        $stateMessage = '';
        if ($state === Item::STATE_BLOCKED) {
            $stateMessage = '<fg=red>Blocked</>';
        } elseif ($state === Item::STATE_PENDING) {
            $stateMessage = '<fg=yellow>Pending</>';
        } elseif ($state === Item::STATE_INDEXED) {
            $stateMessage = '<fg=green>Indexed</>';
        }
        return $stateMessage;
    }

    /**
     * @param $io
     * @param $item
     */
    protected function renderQueueItem($io, $item)
    {
        /** @var $item Item */
        $io->writeln("Queue Item uid: " . $item->getIndexQueueUid());
        $io->writeln("Item type: " . $item->getType());
        $io->writeln("Item uid: " . $item->getRecordUid());
        $io->writeln("Index Configuration: " . $item->getIndexingConfigurationName());
        $io->writeln("Last changed: " . date("d.m.Y - H:i:s", $item->getChanged()));
        $io->writeln("Last indexed: " . date("d.m.Y - H:i:s", $item->getIndexed()));
        $io->writeln("Siteroot: " . $item->getSite()->getRootPageId());
        $io->writeln("Domain: " . $item->getSite()->getDomain());
        $io->writeln("Indexing properties: " . json_encode($item->getIndexingProperties()));

        $stateMessage = $this->getStateMessage($item);
        $io->writeln("State: " . $stateMessage);
    }
}
