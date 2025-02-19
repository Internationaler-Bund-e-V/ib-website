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

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Configurations;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Languages;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use ApacheSolrForTypo3\Solrconsole\Domain\Verification\VerificationService;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The initialize command is responsible to initialize the index queue for a set of sites and index queue configurations.
 */
class SolrIndexVerifyCommand extends AbstractSolrCommand
{
    /**
     * @var ?Sites
     */
    protected ?Sites $sitesHelper = null;

    /**
     * @var array
     */
    protected array $sites = [];

    /**
     * @var array
     */
    protected array $configurations = [];

    /**
     * @var array
     */
    protected array $languages = [];

    /**
     * @var bool
     */
    protected bool $fix = false;

    /**
     * @var ?VerificationService
     */
    protected ?VerificationService $verificationService = null;

    /**
     * Configure command
     */
    public function configure()
    {
        parent::configure();

        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma separated list of site root uids, if none is passed the whole index is being used.', 0);
        $this->addOption('configurations', 'c', InputOption::VALUE_OPTIONAL, 'Comma seperated list of index configurations to initialize, if none is passed all index-configurations get initialize', '*');
        $this->addOption('languages', 'L', InputOption::VALUE_OPTIONAL, 'Comma separated list of language uids, if none is passed all available languages will be processed.', 0);
        $this->addOption('fix', 'F', InputOption::VALUE_NONE, 'Fix the difference by removing invalid records from solr and adding missing records to the index queue.');

        $this->setDescription('Verifies documents from the solr index with the database records');
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

        $configurationHelper = GeneralUtility::makeInstance(Configurations::class);
        $this->configurations = $configurationHelper->run($io, $input);

        $languagesHelper = GeneralUtility::makeInstance(Languages::class);
        $languagesHelper->setLabel('Language uids to verify records for');
        $this->languages = $languagesHelper->run($io, $input);

        $this->fix = $input->getOption('fix');

        if ($this->fix) {
            $confirmed = $io->confirm('Fix the verification issues?');
        } else {
            $confirmed = true;
        }
        return $confirmed;
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws DBALDriverException
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $confirmed = $this->loadOptions($io, $input, $output);
        if (!$confirmed) {
            $io->write('Skipped');
            $io->newLine();
            return self::FAILURE;
        }

        foreach ($this->sites as $site) {
            /** @var $site Site */
            $io->writeln('Checking site with domain: ' . $site->getDomain());

            $result = $this->getVerificationService()->verifySite($site, $this->languages, $this->configurations, $this->fix);

            foreach ($result->getGlobalErrors() as $globalError) {
                $io->writeln('<fg=yellow>Error' . $globalError . '</>');
            }

            $rows = [];
            foreach ($result->getConfigurationVerificationResults() as $configurationVerificationResult) {
                $indexQueueErrors = $configurationVerificationResult->getIndexQueueErrors();
                $missingInTYPO3Count = count($configurationVerificationResult->getMissingInTYPO3());
                $missingInSolrCount = count($configurationVerificationResult->getMissingInSolr());
                $rows[] = [
                    $configurationVerificationResult->getTableName(),
                    '<fg=cyan>' . $configurationVerificationResult->getConfigurationName() . '</>',
                    $configurationVerificationResult->getLanguageUid(),
                    count($configurationVerificationResult->getSolrUids()),
                    count($configurationVerificationResult->getTypo3Uids()),
                    $missingInTYPO3Count === 0 && $missingInSolrCount === 0 ? '<fg=green>OK</>' : '<fg=red>differing</>',
                    '<fg=' . ($missingInSolrCount > 0 ? 'yellow' : 'green') . ">$missingInSolrCount</>",
                    '<fg=' . ($missingInTYPO3Count > 0 ? 'red' : 'green') . ">$missingInTYPO3Count</>",
                    '<fg=' . ($indexQueueErrors > 0 ? 'red' : 'green') . ">$indexQueueErrors</>",
                ];

                $configurationErrors = $configurationVerificationResult->getErrors();

                if (count($configurationErrors) > 0) {
                    $label = 'Error for configuration ' . $configurationVerificationResult->getConfigurationName() . ':';
                    $io->writeln('<fg=yellow>' . $label . '</>');

                    foreach ($configurationErrors as $configurationError) {
                        $io->writeln('<fg=yellow>' . $configurationError . '</>');
                    }
                }
            }

            $headers = ['Table', 'Configuration name', 'Language', 'Entries in solr', 'Entries in TYPO3', 'Result', 'Missing in solr', 'Missing in TYPO3', 'IndexQueue errors'];
            $io->table($headers, $rows);
        }

        if (!$this->fix) {
            $io->writeln('Add <fg=cyan>--fix</> option to remove invalid records from the index and add the missing records to the index queue.');
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
     * @return VerificationService
     */
    public function getVerificationService(): VerificationService
    {
        $this->verificationService = $this->verificationService ?? GeneralUtility::makeInstance(VerificationService::class);
        return $this->verificationService;
    }
}
