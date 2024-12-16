<?php

declare(strict_types=1);

namespace Rms\Ibsearch\Command;

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteInterface as SiteSiteInterface;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\NoSolrConnectionFoundException;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use Rms\Ibsearch\Solr\CustomTikaService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexMyContentsCommand extends AbstractCommand
{
    private OutputInterface $outputInterface;
    private string $baseUrlSolrInterface;
    private string $baseDocumentPath;
    private string $baseUrlPath;
    private array $portals;
    private ConnectionManager $connectionManager;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Index external contents');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputInterface = $output;

        $output->writeln([
            '',
            'Start ib_search...',
            '===================',
            '',
        ]);

        /** @var ConnectionManager $connectionManager */
        $connectionManager = GeneralUtility::makeInstance(ConnectionManager::class);
        $this->connectionManager = $connectionManager;

        /** @var ExtensionConfiguration $extConfig */
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $searchConfig = $extConfig->get('ibsearch');

        $this->baseUrlSolrInterface = $searchConfig['baseUrlSolrInterface'];
        $this->baseDocumentPath = $searchConfig['baseDocumentPath'];
        $this->baseUrlPath = $searchConfig['baseUrlPath'];

        $portals = file_get_contents(GeneralUtility::getFileAbsFileName('EXT:ibsearch/Configuration/Custom/portals.json'));
        $this->portals = json_decode((string) $portals, true);
        //GeneralUtility::requireOnce(PATH_site . 'typo3conf/realurl_conf.php');

        $output->writeln("clearing index ...");
        $this->clearIndex('Standort', 1, 0);
        $this->clearIndex('Angebot', 1, 0);
        $this->clearIndex('Jobs', 1, 0);

        $output->writeln("indexing ...");
        foreach ($this->portals as $portal) {
            $this->outputInterface->writeln($portal['portalName']);

            $tID = intval($portal['tID']);
            $rID = intval($portal['rID']);

            //index locations
            if ($portal['locations']) {
                $this->indexLocations($tID, 0, $rID);
            }
            //index products
            if ($portal['products']) {
                $this->indexProducts($tID, 0, $rID);
            }
            //index jobs
            if ($portal['jobs']['index']) {
                $this->indexIbjobs($tID, 0, $portal['jobs']);
            }
            if ($portal['fwd']) {
                $this->indexFWD($tID, 0);
            }
            //die();
        }

        return 0; // return 0 (SUCCESS) or 1 (FAILURE).
    }

    /**
     * @param int $rootID equals IB portal/domain to index for
     * @param int $langID language ID 0=de, 1=en, etc.
     * @param int $navID redaktionstool navigation id, getting all locations according to navigation
     */
    private function indexLocations(int $rootID, int $langID, int $navID): void
    {
        //$this->outputInterface->writeln([$rootID, $langID, $navID]);
        $entities = json_decode((string) $this->getURL((string) $this->baseUrlSolrInterface . 'solrGetLocations/nav_id:' . $navID), true);
        //debug($this->baseUrlSolrInterface . 'solrGetLocations/nav_id:' . $navID);

        $documents = [];
        //$this->outputInterface->writeln("Standorte: " . count($entities));
        foreach ($entities as $entity) {
            $document = $this->getBaseDocument($rootID, "Standort", (int) ($rootID . $entity['Location']['id']));
            $document->setField('title', strip_tags((string) $entity['Location']['name']));
            $document->setField('federalState_stringS', $entity['Location']['federal_state']);
            $document->setField('street_stringS', $entity['Location']['street']);
            $document->setField('plz_stringS', $entity['Location']['plz']);
            $document->setField('city_stringS', $entity['Location']['city']);
            $document->setField('phone_stringS', $entity['Location']['phone']);
            $document->setField('email_stringS', $entity['Location']['email']);
            $document->setField('fax_stringS', $entity['Location']['fax']);
            $document->setField('categories_stringS', $entity['Categories']);

            if (isset($entity['Contacts'])) {
                $document->setField('contacts_stringS', json_encode($entity['Contacts']));
            } else {
                $document->setField('contacts_stringS', '');
            }

            $document->setField('rootline', array('0-' . $rootID . '/'));
            $document->setField(
                'content',
                strip_tags(html_entity_decode($entity['Location']['short_description'] . " " . $entity['Location']['description']))
            );
            $document->setField('url', '/standort/' . $entity['Location']['id']);
            //$this->outputInterface->writeln($entity['Location']['name']);
            $documents[] = $document;

            /*
            foreach ($entity['Asset'] as $asset) {
              //$this->indexDocument($asset['file_name'], $rootID, $langID, intval('010'.$entity['Location']['id']));
            }
            */
        }

        $connection = $this->connectionManager->getConnectionByRootPageId($rootID, $langID);
        $connection->getWriteService()->addDocuments($documents);
    }

    /**
     * @param int $rootID equals IB portal/domain to index for
     * @param int $langID language ID 0=de, 1=en, etc.
     * @param int $navID redaktionstool navigation id, getting all locations according to navigation
     */
    private function indexProducts(int $rootID, int $langID, int $navID): void
    {
        $entities = json_decode((string) $this->getURL($this->baseUrlSolrInterface . 'solrGetProducts/nav_id:' . $navID), true);
        $documents = [];

        foreach ($entities as $entity) {
            $document = $this->getBaseDocument($rootID, "Angebot", intval($rootID . $entity['Product']['id']));
            $document->setField('title', strip_tags((string) $entity['Product']['name']));
            $document->setField(
                'content',
                strip_tags(html_entity_decode($entity['Product']['short_description'] . " " . $entity['Product']['description']))
            );
            $document->setField('locationTitle_stringS', $entity['Location'][0]['name']);
            $document->setField('street_stringS', $entity['Location'][0]['street']);
            $document->setField('plz_stringS', $entity['Location'][0]['plz']);
            $document->setField('city_stringS', $entity['Location'][0]['city']);
            $document->setField('phone_stringS', $entity['Location'][0]['phone']);
            $document->setField('email_stringS', $entity['Location'][0]['email']);
            $document->setField('fax_stringS', $entity['Location'][0]['fax']);
            $document->setField('url', '/angebot/' . $entity['Product']['id']);
            $document->setField('rootline', array('0-' . $rootID . '/'));
            //$this->outputInterface->writeln($entity['Product']['name']);
            $documents[] = $document;
        }

        $connection = $this->connectionManager->getConnectionByRootPageId($rootID, $langID);
        $connection->getWriteService()->addDocuments($documents);
    }

    /**
     * @param int $rootID equals IB portal/domain to index for
     * @param int $langID language ID 0=de, 1=en, etc.
     * @param array $configuration redaktionstool job filter configuration
     */
    private function indexIbjobs(int $rootID, int $langID, array $configuration): void
    {
        if ($configuration['usePreFilter']) {
            $entities = json_decode(
                (string) $this->getURL(
                    $this->baseUrlSolrInterface . "solrGetIbJobsFiltered/clients:" . $configuration['clients']
                    . "/intern:" . urlencode((string) $configuration['intern'])
                    . "/locations:" . urlencode((string) $configuration['locations'])
                    . "/categories:" . urlencode((string) $configuration['categories'])
                    . "/titles:" . urlencode((string) $configuration['titles'])
                ),
                true
            );
            //$this->outputInterface->writeln(count($entities));
        } else {
            $entities = json_decode((string) $this->getURL($this->baseUrlSolrInterface . 'solrGetIbJobs'), true);
        }

        $documents = [];
        foreach ($entities as $entity) {
            //$this->outputInterface->writeln($entity['Ibjob']['xml_federal_state']);
            $document = $this->getBaseDocument($rootID, "Jobs", intval($rootID . $entity['Ibjob']['xml_a_id']));
            $document->setField('title', $entity['Ibjob']['name']);
            $document->setField('chiffre_stringS', $entity['Ibjob']['xml_chiffre']);
            $document->setField('federalState_stringS', $entity['Ibjob']['xml_federal_state']);
            $document->setField('jobstart_stringS', date('d.m.Y', intval($entity['Ibjob']['xml_job_start'])));
            $document->setField('rootline', array('0-' . $rootID . '/'));
            $document->setField(
                'content',
                html_entity_decode($entity['Ibjob']['xml_description'] . " " . $entity['Ibjob']['xml_trf_description'])
            );
            $document->setField(
                'url',
                '/index.php?id=' . $configuration['detailPageID'] . '&tx_ibjobs[ibjid]=' . $entity['Ibjob']['xml_id']
            );

            //$this->outputInterface->writeln($entity['Ibjob']['name']);
            $documents[] = $document;
        }
        $connection = $this->connectionManager->getConnectionByRootPageId($rootID, $langID);
        $connection->getWriteService()->addDocuments($documents);
    }

    /**
     * @param int $rootID equals IB portal/domain to index for
     * @param int $langID language ID 0=de, 1=en, etc.
     */
    private function indexFWD(int $rootID, int $langID): void
    {
        $entities = json_decode((string) $this->getURL($this->baseUrlSolrInterface . 'solrGetFWDJobs'), true);
        $documents = [];
        //$this->outputInterface->writeln(count($entities));
        foreach ($entities as $entity) {
            $document = $this->getBaseDocument($rootID, "FWD", (int) ($rootID . $entity['Job']['id']));
            $document->setField('title', strip_tags((string) $entity['Job']['name']));
            $document->setField('rootline', array('0-' . $rootID . '/'));
            $document->setField('content', strip_tags(html_entity_decode((string) $entity['Job']['description'])));
            $document->setField('url', '/job/' . $entity['Job']['id']);

            $documents[] = $document;
        }
        $connection = $this->connectionManager->getConnectionByRootPageId($rootID, $langID);
        $connection->getWriteService()->addDocuments($documents);
    }

    protected function indexDocument(string $relativeFilePath, int $rootID, int $langID, int $id): void
    {
        $filePath = $this->baseDocumentPath . $relativeFilePath;
        $extractedData = [];

        //if (empty($configuration)) {
        /** @var ExtensionConfiguration $extConfiguration */
        $extConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $configuration = $extConfiguration->get('tika');
        //}

        /** @var CustomTikaService $customTikaService */
        $customTikaService = GeneralUtility::makeInstance(CustomTikaService::class, $configuration);

        //$this->outputInterface->writeln("sending to tika...");
        //$this->outputInterface->writeln($filePath);

        $extractedData = json_decode((string) $customTikaService->customExtractText($filePath), true);
        //$extractedData['meta'] = $customTikaService->customExtractMetadata($filePath);

        $documents = [];

        $document = $this->getBaseDocument($rootID, "Dokument", $id);
        $document->setField('title', $extractedData[0]['resourceName']);
        $document->setField('doctype_stringS', $extractedData[0]['Content-Type']);
        $document->setField('content', $extractedData[0]['X-TIKA:content']);

        $document->setField('url', $this->baseUrlPath . $relativeFilePath);
        $documents[] = $document;

        $connection = $this->connectionManager->getConnectionByPageId($rootID, $langID);
        $connection->getWriteService()->addDocuments($documents);
    }

    private function getURL(string $url): string|bool
    {
        $session = curl_init($url);

        if (!$session) {
            return false;
        }

        // Don't return HTTP headers. Do return the contents of the call
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        //debug(curl_exec($session));die();
        if (curl_exec($session) == "err101") {
            return false;
            /*
            return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                $this->request,
                'The requested page does not exist',
                ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
            );
            */
        } else {
            return curl_exec($session);
        }
    }

    /**
     * Creates a Solr document with the basic / core fields set already.
     *
     * @param int $rootPageId root page id
     * @param string $type The type of the document
     * @param int $id The id of the document
     * @return Document A basic Solr document
     */
    protected function getBaseDocument(int $rootPageId, string $type, int $id): Document
    {
        /** @var SiteRepository $siteRepository */
        $siteRepository = GeneralUtility::makeInstance(SiteRepository::class);

        /** @var SiteSiteInterface $site */
        $site = $siteRepository->getSiteByRootPageId($rootPageId);

        /** @var Document $document */
        $document = GeneralUtility::makeInstance(Document::class);

        // required fields
        $document->setField('id', $type . $id);
        $document->setField('variantId', $type . $id);
        $document->setField('type', $type);
        $document->setField('appKey', 'EXT:solr');
        $document->setField('access', ['r:0']);

        // site, siteHash
        $document->setField('site', $site->getDomain());
        $document->setField('siteHash', $site->getSiteHash());

        // uid, pid
        //$document->setField('uid', $itemRecord['uid']);
        //$document->setField('pid', 1);

        return $document;
    }

    /**
     * Remove all from index
     *
     * @throws NoSolrConnectionFoundException
     */
    public function clearIndex(string $type, int $rootID, int $langID): void
    {
        $connection = $this->connectionManager->getConnectionByPageId($rootID, $langID);
        $connection->getWriteService()->deleteByType($type, true);
    }
}
/*
class CustomTikaService extends AppService
{
    public function customExtractText(string $filePath): string|false|null
    {
        $tikaCommand = ShellUtility::getLanguagePrefix()
            . CommandUtility::getCommand('java')
            . ' -Dapple.awt.UIElement=true'
            . ' -Dfile.encoding=UTF8' // forces UTF8 output
            . ' -jar ' . escapeshellarg((string)FileUtility::getAbsoluteFilePath($this->configuration['tikaPath']))
            . ' -J'
            . ' ' . ShellUtility::escapeShellArgument($filePath);
        //. ' 2> /dev/null';

        $extractedText = shell_exec($tikaCommand);

        return $extractedText;
    }
}
*/
