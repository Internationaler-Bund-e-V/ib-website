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
use ApacheSolrForTypo3\Solr\Domain\Search\Query\SearchQuery;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Fields;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Ids;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Sites;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Types;
use ApacheSolrForTypo3\Solrconsole\Command\OptionHelper\Uids;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The initialize command is responsible to initialize the index queue for a set of sites and index queue configurations.
 */
class SolrIndexGetCommand extends AbstractSolrCommand
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
    private $uids;

    /**
     * @var array
     */
    private $ids;

    /**
     * @var array
     */
    private $types;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var int
     */
    private $language;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var int
     */
    private $start;


    /**
     * Configure command
     */
    public function configure()
    {
        parent::configure();

        $this->addOption('sites', 's', InputOption::VALUE_OPTIONAL, 'Comma separated list of site root uids, if none is passed the whole index is being used.', 0);
        $this->addOption('types', 't', InputOption::VALUE_OPTIONAL, 'Comma separated list of types (table of the record).', '*');
        $this->addOption('fields', 'f', InputOption::VALUE_OPTIONAL, 'Comma separated list of fields (table of the record).', '*');
        $this->addOption('uids', 'u', InputOption::VALUE_OPTIONAL, 'Comma separated list of record uids.', '*');
        $this->addOption('ids', 'i', InputOption::VALUE_OPTIONAL, 'Comma separated list of solr document ids.', '*');
        $this->addOption('language', 'L', InputOption::VALUE_OPTIONAL, 'Language uid to fetch core for.', 0);

        $this->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Page of items that should be shown.', 1);
        $this->addOption('per-page', null, InputOption::VALUE_OPTIONAL, 'Items count per page.', 10);

        $this->setDescription('Retreives documents from the solr index');
    }


    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws DBALDriverException
     */
    protected function loadOptions(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $this->sites = $this->getSitesHelper()->run($io, $input, $output);

        /** @var $uidsHelper Uids */
        $uidsHelper = GeneralUtility::makeInstance(Uids::class);
        $uidsHelper->setLabel('Record uids');
        $this->uids = $uidsHelper->run($io, $input);

        /** @var $idsHelper Ids */
        $idsHelper = GeneralUtility::makeInstance(Ids::class);
        $this->ids = $idsHelper->run($io, $input);

        /** @var $typesHelper Types */
        $typesHelper = GeneralUtility::makeInstance(Types::class);
        $this->types = $typesHelper->run($io, $input);

        /** @var $fieldsHelper Fields */
        $fieldsHelper = GeneralUtility::makeInstance(Fields::class);
        $this->fields = $fieldsHelper->run($io, $input);

        $this->language = (int)$input->getOption('language');
        $io->writeln('Language: ' . $this->language);
        $io->newLine(1);

        $this->page = $input->getOption('page');
        $this->page = (int)($this->page ?? 1);
        $io->writeln('Page: ' . $this->page);
        $io->newLine(1);

        $this->perPage = $input->getOption('per-page');
        $this->perPage = (int)($this->perPage ?? 10);
        $io->writeln('Item count per page: ' . $this->perPage);

        $this->start = ($this->page - 1) * $this->perPage;

        $confirmed = $io->confirm('Is this correct?', true);
        return $confirmed;
    }


    /**
     * @param $domain
     * @return array
     */
    protected function getParamsForDomain($domain)
    {
        $params = [];
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

        if (count($this->fields)) {
            $params['fl'] = implode(',', $this->fields);
        }
        if (count($filterQuery)) {
            $params['fq'] = implode(' AND ', $filterQuery);
        }
        return $params;
    }


    /**
     * @param SymfonyStyle $io
     * @param Site $site
     * @param $domain
     * @throws \ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException
     */
    protected function getSolrIndexResults(SymfonyStyle $io, Site $site, $domain)
    {
        $solrConnection = $this->getSolrConnectionForRootPageUidAndLanguage($site->getRootPageId(), $this->language);
        $params = $this->getParamsForDomain($domain);
        $readService = $solrConnection->getReadService();
        $searchQuery = new SearchQuery();
        $searchQuery->setQuery('*')->setStart($this->start)->setRows($this->perPage);
        foreach($params as $paramName => $paramValue) {
            $searchQuery->addParam($paramName, $paramValue);
        }

        $result = $readService->search($searchQuery);
        $response = $result->getParsedData();
        $numFound = $response->response->numFound;
        $maxPages = ceil($numFound / $this->perPage);
        $coreName = $readService->getPrimaryEndpoint()->getCore();

        if ($numFound == 0) {
            $io->writeln("<fg=red>No documents found in core {$coreName} for site {$site->getRootPageId()}</>");
            return;
        }
        if ($this->page > $maxPages) {
            $io->writeln("<fg=red>We found some results, however you specified to start on a page, which doesn't exist");
            return;
        }

        $documentText = $numFound == 1 ? 'document' : 'documents';
        $io->writeln("<fg=green>{$numFound} {$documentText} found in core {$coreName}</>, showing page {$this->page} of {$maxPages} ({$this->perPage} records per page)");
        $io->newLine(1);
        if ($numFound > 0) {
            $itemCount = 0;
            $table = new Table($io);
            $table->setHeaders(['Keys', 'Values']);
            foreach ($response->response->docs as $document) {
                if (!$document instanceof Document) {
                    continue;
                }
                foreach ($document->getFields() as $key => $value) {
                    $displayValue = is_array($value) ? implode(', ', $value) : $value;
                    if (strlen((string)$displayValue) > 100) {
                        $displayValue = substr($displayValue, 0, 100) . '...';
                    }
                    $table->addRow([$key, $displayValue]);
                }
                if (++$itemCount < $numFound && $itemCount < $this->perPage) {
                    $table->addRow(new TableSeparator());
                }
            }
            $table->render();
        }
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
        $confirmed = $this->loadOptions($io, $input, $output);
        if (!$confirmed) {
            $io->write('Skipped');
            $io->newLine(1);
            return 1;
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
            try {
                $this->getSolrIndexResults($io, $site, $domain);
            } catch (NoSolrConnectionFoundException $ex) {
                $io->writeln("No solr connection found for site {$site->getRootPageId()}, language {$this->language}.");
            } catch (\Exception $ex) {
                $io->writeln("Solr connection could not be initialized for site {$site->getRootPageId()}, language {$this->language}.");
            }
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
     */
    public function setConnectionManager(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }


    /**
     * @param int $rootPageUid
     * @param int $language
     * @return SolrConnection
     * @throws \ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException
     */
    protected function getSolrConnectionForRootPageUidAndLanguage(int $rootPageUid, int $language = 0): SolrConnection
    {
        return $this->getConnectionManager()->getConnectionByPageId($rootPageUid, $language);
    }

}
