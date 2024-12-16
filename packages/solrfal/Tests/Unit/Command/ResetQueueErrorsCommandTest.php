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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\Context;

use ApacheSolrForTypo3\Solrfal\Command\ResetQueueErrorsCommand;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ResetQueueErrorsCommandTest
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class ResetQueueErrorsCommandTest extends UnitTest
{
    /**
     * @test
     */
    public function testResetQueueErrorsCommandCallsItemRepositoryFlushMethod()
    {
        $command = new class('solrfal:resetQueueErrors') extends ResetQueueErrorsCommand {
            public $itemRepository;

            public $symfonyStyle;

            protected function getItemRepository(): ItemRepository
            {
                return $this->itemRepository;
            }

            public function callProtectedExecute(InputInterface $input, OutputInterface $output)
            {
                $this->execute($input, $output);
            }

            protected function getSymfonyStyle(InputInterface $input, OutputInterface $output): SymfonyStyle
            {
                return $this->symfonyStyle;
            }
        };

        /* @var $repositoryMock ItemRepository */
        $command->itemRepository = $this->getDumbMock(ItemRepository::class);
        $command->symfonyStyle = $this->getDumbMock(SymfonyStyle::class);

        $command->itemRepository->expects(self::once())->method('flushAllErrors')->willReturn(99);
        $inputMock = $this->getDumbMock(Input::class);
        $outputMock = $this->getDumbMock(Output::class);

        $command->callProtectedExecute($inputMock, $outputMock);
    }
}
