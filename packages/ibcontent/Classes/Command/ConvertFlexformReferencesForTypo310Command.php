<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertFlexformReferencesForTypo310Command extends AbstractCommand
{
    private $counter = 0;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        parent::configure();
        $this->setDescription('convert pi_flexform references to typo3 v10 compatible db references');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder_ttcontent = $pool->getConnectionForTable('tt_content')->createQueryBuilder();
        $queryBuilder_sysfile = $pool->getConnectionForTable('sys_file')->createQueryBuilder();

        $queryBuilder_ttcontent
            ->getRestrictions()
            ->removeAll();

        $statement_ttcontent = $queryBuilder_ttcontent
            ->select('uid', 'pi_flexform', 'list_type', 'pid')
            ->from('tt_content')
            ->where(
                $queryBuilder_ttcontent->expr()->or($queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_startpageslider')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_bubbleslider')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_accordion')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_mediaelement')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_tiles')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_textextended')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_contentslider')
                ), $queryBuilder_ttcontent->expr()->eq(
                    'list_type',
                    $queryBuilder_ttcontent->createNamedParameter('ibcontent_sidebardownloads')
                ))
            )
            //->where($queryBuilder_ttcontent->expr()->eq('uid', 7386))
            //->setMaxResults(100)
            ->executeQuery();

        while ($row = $statement_ttcontent->fetchAllAssociative()) {
            $xml = simplexml_load_string((string)$row['pi_flexform']);
            $update_db = false;

            if ($xml) {
                $xpaths = [
                    "//field[@index='slideImage']/value[@index='vDEF']",
                    "//field[@index='image']/value[@index='vDEF']",
                    "//field[@index='accordionImage']/value[@index='vDEF']",
                    "//field[@index='bubbleImage']/value[@index='vDEF']",
                    "//field[@index='settings.image']/value[@index='vDEF']",
                    "//field[@index='settings.videoPoster']/value[@index='vDEF']",
                    "//field[@index='settings.videoMP4']/value[@index='vDEF']",
                    "//field[@index='settings.videoWEBM']/value[@index='vDEF']",
                    "//field[@index='settings.videoOGG']/value[@index='vDEF']",
                    "//field[@index='file']/value[@index='vDEF']",
                ];

                $xml_string = $xml->asXML();

                foreach ($xpaths as $path) {
                    $flex_reference = $xml->xpath($path);
                    if ($flex_reference !== []) {
                        $xml_string = $this->replaceNode(
                            $flex_reference,
                            $queryBuilder_sysfile,
                            (string)$row['list_type'],
                            (string)$xml_string,
                            (int)$row['uid'],
                            (int)$row['pid']
                        );
                        $update_db = true;
                    }
                }

                if ($update_db) {
                    $queryBuilder_ttcontent
                        ->update('tt_content')
                        ->where(
                            $queryBuilder_ttcontent->expr()->eq('uid', $row['uid'])
                        )->set('pi_flexform', $xml_string)->executeStatement();
                }
            }
        }

        print_r("\n\n" . $this->counter . " items replaces \n\n");

        return 0; // return 0 (SUCCESS) or 1 (FAILURE).
    }

    /**
     * @param $flex_reference
     * @param $queryBuilder_sysfile
     * @param $list_type
     * @param $xml_string
     * @param $uid
     * @param $pid
     * @return string
     */
    private function replaceNode(array $flex_reference, mixed $queryBuilder_sysfile, string $list_type, string $xml_string, int $uid, int $pid)
    {
        foreach ($flex_reference as $node) {
            if (strpos((string)$node, 'fileadmin') !== false) {
                if ((string)$node !== "") {
                    $node_short = (string)str_replace('fileadmin', '', (string)$node);

                    $statement_sysfile = $queryBuilder_sysfile
                        ->select('*')
                        ->from('sys_file')
                        //->where($queryBuilder_sysfile->expr()->like('identifier', "'%" . (string)$node_short . "'"))
                        ->where($queryBuilder_sysfile->expr()->eq('identifier', "'" . $node_short . "'"))
                        ->execute();
                    //->fetchAll();

                    while ($row_sysfile = $statement_sysfile->fetch()) {
                        print_r($uid . ' ----- ' . $node . ' -- ' . $row_sysfile['uid'] . ' -- ' . $list_type . "\n");
                        $xml_string = str_replace((string)$node, $row_sysfile['uid'], (string)$xml_string);
                        $this->counter++;
                    }
                }
            }
        }

        return $xml_string;
    }
}
