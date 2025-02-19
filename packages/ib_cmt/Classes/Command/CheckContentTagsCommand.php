<?php

declare(strict_types=1);

namespace IB\IbCmt\Command;

use IB\IbCmt\Domain\Model\Content;
use IB\IbCmt\Domain\Repository\ContentRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class CheckContentTagsCommand extends AbstractCommand
{
    /**
     * @var ContentRepository
     */
    private $contentRepository = null;

    /** @var PersistenceManager */
    protected $persistenceManager = null;

    /** @var int<0, max> $storagePid */
    private int $storagePid = 0;
    private string $pathToRTJSON = '';
    private string $mailContent = "";
    private array $keepContents = array();
    private array $critStrings = array();

    public function injectContentRepository(ContentRepository $contentRepository): void
    {
        $this->contentRepository = $contentRepository;
    }

    public function __construct(PersistenceManager $persistenceManager)
    {
        parent::__construct();
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Checks content elements on the occurrence of tags and strings, e.g. iframe, youtube.com');
        $this->addArgument(
            'terms',
            InputArgument::REQUIRED,
            'Terms to search for, multiple termss comma separated, no spaces, e.g. iframe,script'
        );
        $this->addArgument('email', InputArgument::REQUIRED, 'multiple addresses comma separated, no spaces');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //get ext settings
        /** @var ExtensionConfiguration $conf */
        $conf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extensionConfiguration = $conf->get('ib_cmt');
        $this->pathToRTJSON = $extensionConfiguration['pathRTJSON'];
        // @phpstan-ignore-next-line
        $this->storagePid = (int) $extensionConfiguration['cmtStoragePid'];

        //$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        //$this->contentRepository = $objectManager->get(ContentRepository::class);
        $querySettings = $this->contentRepository->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);

        $this->contentRepository->setDefaultQuerySettings($querySettings);
        //$this->persistenceManager = $objectManager->get("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");

        $output->writeln([
            'Checking content...',
            '===================',
            '',
        ]);

        $fields = ['bodytext', 'pi_flexform'];
        $this->critStrings = explode(",", (string) $input->getArgument('terms'));

        // Get a query builder for a query on table "tt_content"
        $tableName = 'tt_content';

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $pool->getQueryBuilderForTable($tableName);

        $constraints = [];
        foreach ($fields as $field) {
            foreach ($this->critStrings as $critString) {
                $constraints[] = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($critString) . '%')
                );
            }
        }
        $statement = $queryBuilder
            ->select('pid', 'uid', 'tstamp')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                $queryBuilder->expr()->or(...$constraints)
            );

        $result = $statement->executeQuery()->fetchAllAssociative();
        $affectedRecords = count($result);

        $output->writeln("Checked fields:");
        $output->writeln($fields);
        $output->writeln('');
        $output->writeln("Critical strings:");
        $this->mailContent .= "Checked terms: <br>";
        $this->mailContent .= implode(",", $this->critStrings) . "<br><br>";
        $output->writeln($this->critStrings);
        $output->writeln('');

        //add/check typo3 content
        $this->updateDatabase($result, 0);
        $this->importJSON();
        $this->checkNews();

        $this->contentRepository->cleanContent($this->keepContents);

        //send mail if content found
        if ($affectedRecords > 0) {
            $addresses = explode(',', (string) $input->getArgument('email'));
            $this->sendReport($this->mailContent, $addresses);
        }

        return 0; // return 0 (SUCCESS) or 1 (FAILURE).
    }

    private function updateDatabase(array $result, int $contentType): void
    {
        foreach ($result as $row) {
            //$output->writeln("pid: " . $row['pid'] . "   uid: " . $row['uid'] . "    tstamp: " . $row['tstamp']);
            //check and update database
            $content = $this->contentRepository->findByContentid((string) $row['uid']);
            if (count($content) == 1) {
                $tmpContent = $content[0];
                $this->keepContents[] = $tmpContent->getUid();
                if (($row['tstamp'] != $tmpContent->getTstampallowed()) && ($tmpContent->getAllowed())) {
                    $tmpContent->setComment(" --- edited --- " . $tmpContent->getComment());
                    $tmpContent->setAllowed(false);
                    $tmpContent->setContenttstamp($row['tstamp']);
                    $tmpContent->setContentparentid(intval($row['pid']));
                    $this->contentRepository->update($tmpContent);
                    $this->persistenceManager->persistAll();
                }
            } else {
                $content = new Content();
                $content->setAllowed(false);
                $content->setContentid(intval($row['uid']));
                $content->setContentparentid(intval($row['pid']));
                $content->setContenttstamp($row['tstamp']);
                $content->setTstampallowed($row['tstamp']);
                $content->setContenttype($contentType);
                if ($contentType == 1) {
                    $content->setRtcontenttype($row['rtcontenttype']);
                    $content->setComment($row['comment']);
                }

                $content->setPid($this->storagePid);
                $this->contentRepository->add($content);
                $this->persistenceManager->persistAll();
                $this->keepContents[] = $content->getUid();
            }
        }
    }

    private function importJSON(): void
    {
        $json = json_decode((string) file_get_contents($this->pathToRTJSON), true);
        $rtEntities = array(
            'locations' => 'Location',
            'products' => 'Product',
            'customcontent' => 'Customcontent',
        );

        $i = 0;
        foreach ($rtEntities as $entity => $name) {
            $row = array();
            foreach ($json[$entity] as $data) {
                $tmpData = array();
                $tmpData['uid'] = intval($data[$name]['id']);
                $tmpData['pid'] = 'n/a';
                $tmpData['tstamp'] = strtotime((string) $data[$name]['modified']);
                $tmpData['rtcontenttype'] = $i;
                $tmpData['comment'] = '';
                if ($name == 'Customcontent') {
                    if ($data[$name]['location_id'] != null) {
                        $tmpData['comment'] = "Standort: " . $data[$name]['location_id'];
                    }
                    if ($data[$name]['product_id'] != null) {
                        $tmpData['comment'] .= "Angebot: " . $data[$name]['product_id'];
                    }
                }
                $row[] = $tmpData;
            }
            $this->updateDatabase($row, 1);
            $i++;
        }
    }

    private function checkNews(): void
    {
        $fields = ['bodytext', 'teaser'];

        // Get a query builder for a query on table "tt_content"
        $tableName = 'tx_news_domain_model_news';

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $pool->getQueryBuilderForTable($tableName);

        $constraints = [];
        foreach ($fields as $field) {
            foreach ($this->critStrings as $critString) {
                $constraints[] = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($critString) . '%')
                );
            }
        }
        $statement = $queryBuilder
            ->select('pid', 'uid', 'tstamp', 'title')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                $queryBuilder->expr()->or(...$constraints)
            );

        $result = $statement->executeQuery()->fetchAllAssociative();
        $this->updateDatabase($result, 2);
    }

    private function sendReport(string $report, array $addresses): void
    {
        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);

        $report .= "Betroffene Typo3 Contentelemente: " . count($this->contentRepository->getTypo3Content()) . "</br>";
        $report .= "Betroffene Typo3 Newselemente: " . count($this->contentRepository->getNewsContent()) . "</br>";
        $report .= "Betroffene Redaktionstool Contentelemente: " . count($this->contentRepository->getRtContent()) . "</br>";

        $mail->setSubject('Typo3 Content Report');
        $mail->setFrom(array('typo3@ib.de' => 'T3 Report'));
        $mail->setTo($addresses);
        //$mail->setBody('T3 Content Report');
        //$mail->format('T3 Content Report');
        $mail->html($report);
        $mail->send();
    }
}
