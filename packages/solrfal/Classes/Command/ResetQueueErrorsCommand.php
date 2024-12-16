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

namespace ApacheSolrForTypo3\Solrfal\Command;

use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command call to reset the errors of the solrfal queue.
 */
class ResetQueueErrorsCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     * @noinspection PhpUnused
     */
    public function configure()
    {
        $this->setDescription('EXT:solrfal: Resets the errors of the solr file index queue');
    }

    /**
     * @inheritDoc
     * @noinspection PhpUnused
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getSymfonyStyle($input, $output);
        $io->title($this->getDescription());
        try {
            $itemCountWithErrors = $this->getItemRepository()->flushAllErrors();
            $io->success('All errors(' . $itemCountWithErrors . ') in the queue have been reset.');
        } catch (Exception $e) {
            $io->error('Error resetting all queue errors');
            return 1;
        }
        return 0;
    }

    /**
     * @return ItemRepository
     */
    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    protected function getSymfonyStyle(InputInterface $input, OutputInterface $output): SymfonyStyle
    {
        return new SymfonyStyle($input, $output);
    }
}
