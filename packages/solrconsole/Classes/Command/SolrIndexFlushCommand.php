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

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Fields;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Languages;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The solr:index:flush command is responsible for flushing the index of given cores.
 */
class SolrIndexFlushCommand extends AbstractSolrCommand
{
    /**
     * @var Sites
     */
    protected $sitesHelper;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var array
     */
    private $sites;

    /**
     * @var array
     */
    private $languages;

    /**
     * Configure command
     * @noinspection PhpUnused
     */
    public function configure()
    {
        parent::configure();

        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma separated list of site root uids, if none is passed the whole index is being used.', 0);
        $this->addOption('languages', 'L', InputOption::VALUE_OPTIONAL, 'Comma separated list of language uids, if none is passed all available languages will be processed.', 0);

        $this->setDescription('Flushes the index of Apache Solr.');
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|mixed
     * @throws DBALDriverException
     */
    protected function loadOptions(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $this->sites = $this->getSitesHelper()->run($io, $input, $output);

        /* @var Fields $languagesHelper */
        $languagesHelper = GeneralUtility::makeInstance(Languages::class);
        $languagesHelper->setLabel('Language uids to flush index for');
        $this->languages = $languagesHelper->run($io, $input);

        return $io->confirm('Is this correct?', true);
    }

    /**
     * @return string
     */
    protected function getFlushRawQuery(): string
    {
        return '*:*';
    }

    /**
     * @param SymfonyStyle $io
     * @param Site $site
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    protected function deleteSolrIndexDocuments(SymfonyStyle $io, Site $site)
    {
        $availableLanguageIds = $site->getAvailableLanguageIds();
        if (empty($this->languages)) {
            $this->languages = array_merge([0], $availableLanguageIds);
        }
        foreach ($this->languages as $language) {
            try {
                $solrConnection = $this->getSolrConnectionForRootPageUidAndLanguage($site->getRootPageId(), (int)$language);
                $writeService = $solrConnection->getWriteService();
                $coreName = $writeService->getPrimaryEndpoint()->getCore();
                $deleteRawQuery = $this->getFlushRawQuery();
                $writeService->deleteByQuery($deleteRawQuery);
                $io->writeln("Deleting entries matching <fg=yellow>{$deleteRawQuery}</> on core <fg=yellow>{$coreName}</>, site <fg=yellow>{$site->getRootPageId()}</>, language <fg=yellow>{$language}</>.");
            } catch (NoSolrConnectionFoundException $ex) {
                $io->writeln("No solr connection found for site {$site->getRootPageId()}, language {$language}.");
            } catch (Exception $ex) {
                $io->writeln("Solr connection could not be initialized for site {$site->getRootPageId()}, language {$language}.");
            }
        }
    }

    /**
     * Executes the command to update the connection
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return integer
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnused
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $confirmed = $this->loadOptions($io, $input, $output);
        if (!$confirmed) {
            $io->write('Skipped');
            $io->newLine();
            return 1;
        }
        foreach ($this->sites as $site) {
            /* @var Site $site */
            $solrConfiguration = $site->getSolrConfiguration();
            if (!$solrConfiguration->getEnabled()) {
                continue;
            }
            $this->deleteSolrIndexDocuments($io, $site);
        }

        return 0;
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
     * @noinspection PhpUnused
     */
    public function setSitesHelper(Sites $sitesHelper)
    {
        $this->sitesHelper = $sitesHelper;
    }

    /**
     * @return ConnectionManager
     */
    public function getConnectionManager(): ConnectionManager
    {
        if (!$this->connectionManager instanceof ConnectionManager) {
            $this->connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
        }
        return $this->connectionManager;
    }

    /**
     * @param ConnectionManager $connectionManager
     * @noinspection PhpUnused
     */
    public function setConnectionManager(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * @param int $rootPageUid
     * @param int $language
     * @return SolrConnection
     * @throws NoSolrConnectionFoundException
     */
    protected function getSolrConnectionForRootPageUidAndLanguage(int $rootPageUid, int $language = 0): SolrConnection
    {
        return $this->getConnectionManager()->getConnectionByPageId($rootPageUid, $language);
    }
}
