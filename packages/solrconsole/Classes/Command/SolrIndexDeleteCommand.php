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
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Fields;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Ids;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Languages;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Types;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Uids;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The initialize command is responsible to initialize the index queue for a set of sites and index queue configurations.
 */
class SolrIndexDeleteCommand extends AbstractSolrCommand
{
    /**
     * @var Sites
     */
    protected Sites $sitesHelper;

    /**
     * @var ConnectionManager
     */
    protected ConnectionManager $connectionManager;

    /**
     * @var array
     */
    private array$sites;

    /**
     * @var array
     */
    private array$uids;

    /**
     * @var array
     */
    private array $ids;

    /**
     * @var array
     */
    private array $types;

    /**
     * @var array
     */
    private array $languages;

    /**
     * Configure command
     */
    public function configure()
    {
        parent::configure();

        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma separated list of site root uids, if none is passed the whole index is being used.', 0);
        $this->addOption('types', 't', InputOption::VALUE_OPTIONAL, 'Comma separated list of types (table of the record).', '*');
        $this->addOption('uids', 'u', InputOption::VALUE_OPTIONAL, 'Comma separated list of record uids.', '*');
        $this->addOption('ids', 'i', InputOption::VALUE_OPTIONAL, 'Comma separated list of solr document ids.', '*');
        $this->addOption('languages', 'L', InputOption::VALUE_OPTIONAL, 'Comma separated list of language uids, if none is passed all available languages will be processed.', '*');

        $this->setDescription('Deletes documents from the solr index');
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

        /** @var Uids $uidsHelper */
        $uidsHelper = GeneralUtility::makeInstance(Uids::class);
        $uidsHelper->setLabel('Record uids to delete');
        $this->uids = $uidsHelper->run($io, $input);

        /** @var Ids $idsHelper */
        $idsHelper = GeneralUtility::makeInstance(Ids::class);
        $idsHelper->setLabel('Document ids to delete');
        $this->ids = $idsHelper->run($io, $input);

        /** @var Types $typesHelper */
        $typesHelper = GeneralUtility::makeInstance(Types::class);
        $typesHelper->setLabel('Types to delete');
        $this->types = $typesHelper->run($io, $input);

        /** @var Fields $languagesHelper */
        $languagesHelper = GeneralUtility::makeInstance(Languages::class);
        $languagesHelper->setLabel('Language uids to delete documents for');
        $this->languages = $languagesHelper->run($io, $input);

        $confirmed = $io->confirm('Is this correct?', true);
        return $confirmed;
    }

    /**
     * @param string $domain
     * @return string
     */
    protected function getDeleteRawQuery($domain): string
    {
        $params = '';
        $filterQuery = ['site:' . $domain];

        $uidQuery = [];
        foreach ($this->uids as $uid) {
            $uidQuery[] = 'uid:' . $uid;
        }
        if (count($uidQuery)) {
            $filterQuery[] = '(' . implode(' OR ', $uidQuery) . ')';
        }

        $idQuery = [];
        foreach ($this->ids as $id) {
            $idQuery[] = 'id:' . $id;
        }
        if (count($idQuery)) {
            $filterQuery[] = '(' . implode(' OR ', $idQuery) . ')';
        }

        $typeQuery = [];
        foreach ($this->types as $type) {
            $typeQuery[] = 'type:' . $type;
        }
        if (count($typeQuery)) {
            $filterQuery[] = '(' . implode(' OR ', $typeQuery) . ')';
        }

        if (count($filterQuery)) {
            $params = implode(' AND ', $filterQuery);
        }
        return $params;
    }

    /**
     * @param SymfonyStyle $io
     * @param Site $site
     * @param $domain
     */
    protected function deleteSolrIndexDocuments(SymfonyStyle $io, Site $site, $domain): void
    {
        $availableLanguageIds = $site->getAvailableLanguageIds();
        if (empty($this->languages)) {
            $this->languages = array_unique(array_merge([0], $availableLanguageIds));
        }
        foreach ($this->languages as $language) {
            try {
                $solrConnection = $this->getSolrConnectionForRootPageUidAndLanguage($site->getRootPageId(), (int)$language);
                $writeService = $solrConnection->getWriteService();
                $coreName = $writeService->getPrimaryEndpoint()->getCore();
                $deleteRawQuery = $this->getDeleteRawQuery($domain);
                $writeService->deleteByQuery($deleteRawQuery);
                $io->writeln("Deleting entries matching <fg=yellow>{$deleteRawQuery}</> on core <fg=yellow>{$coreName}</>, site <fg=yellow>{$site->getRootPageId()}</>, language <fg=yellow>{$language}</>.");
            } catch (NoSolrConnectionFoundException $ex) {
                $io->writeln("No solr connection found for site {$site->getRootPageId()}, language {$language}.");
            } catch (\Exception $ex) {
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
            /* @var Site $site */
            $solrConfiguration = $site->getSolrConfiguration();
            if (!$solrConfiguration->getEnabled()) {
                continue;
            }
            $domain = $site->getDomain();
            if (empty($domain)) {
                $io->writeln("<fg=yellow>A domain record for site {$site->getRootPageId()} missing</>");
                continue;
            }
            $this->deleteSolrIndexDocuments($io, $site, $domain);
        }

        return self::SUCCESS;
    }

    /**
     * @return Sites
     */
    public function getSitesHelper(): Sites
    {
        if (!isset($this->sitesHelper)) {
            $this->sitesHelper = GeneralUtility::makeInstance(Sites::class);
        }

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
     * @return ConnectionManager
     */
    public function getConnectionManager(): ConnectionManager
    {
        if (!isset($this->connectionManager)) {
            $this->connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
        }
        return $this->connectionManager;
    }

    /**
     * @param ConnectionManager $connectionManager
     */
    public function setConnectionManager(ConnectionManager $connectionManager): void
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
