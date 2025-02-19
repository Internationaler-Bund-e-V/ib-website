<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertFlexformFileReferencesForTypo312Command extends AbstractCommand
{
    private int $counter = 0;

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('convert file references in pi_flexform to typo3 v12 compatible references');
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
        $xpaths = [
            "//field[@index='slideImage']/value[@index='vDEF']", # startpageslider
            "//field[@index='settings.image']/value[@index='vDEF']", # mediaelement
            "//field[@index='bubbleImage']/value[@index='vDEF']", # bubbleslider
            "//field[@index='image']/value[@index='vDEF']", # contentslider
            "//field[@index='accordionImage']/value[@index='vDEF']", # accordion
            "//field[@index='settings.videoPoster']/value[@index='vDEF']", # mediaelement
            "//field[@index='settings.videoMP4']/value[@index='vDEF']", # mediaelement
            "//field[@index='settings.videoWEBM']/value[@index='vDEF']", # mediaelement
            "//field[@index='settings.videoOGG']/value[@index='vDEF']", # mediaelement
            "//field[@index='file']/value[@index='vDEF']",
        ];

        foreach ($xpaths as $xpath) {
            $this->parseForXpath($xpath);
        }

        print_r("\n\n" . $this->counter . " items replaced \n\n");

        return 0; // return 0 (SUCCESS) or 1 (FAILURE).
    }

    protected function parseForXpath(string $xpath): void
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
                $queryBuilder_ttcontent->expr()->or(
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_startpageslider')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_mediaelement')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_bubbleslider')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_contentslider')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_tiles')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_accordion')
                    ),
                    #$queryBuilder_ttcontent->expr()->eq(
                    #    'list_type',
                    #    $queryBuilder_ttcontent->createNamedParameter('ibcontent_textextended')
                    #),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_sidebardownloads')
                    ),
                )
            )
            //->where($queryBuilder_ttcontent->expr()->eq('uid', 14862))
            ->orderBy('pid', 'ASC')
            #->setMaxResults(10)
            ->executeQuery();

        $rows = $statement_ttcontent->fetchAllAssociative();
        foreach ($rows as $row) {
            $xml = simplexml_load_string((string)$row['pi_flexform']);

            if ($xml) {
                $xml_result = $this->updateXmlString($xpath, (string)$row['pi_flexform'], (int)$row['uid']);
                if ($xml_result !== '') {
                    // Check if $xml_string is valid XML
                    if (simplexml_load_string($xml_result) === false) {
                        print_r("Invalid XML for tt_content pid:" . $row['pid'] . ' uid:' . $row['uid'] . "\n");
                        continue;
                    }

                    // save modified string
                    $queryBuilder_ttcontent->update('tt_content')
                        ->where(
                            $queryBuilder_ttcontent->expr()->eq('uid', $row['uid'])
                        )
                        ->set('pi_flexform', $xml_result)->executeStatement();
                }
            }
        }
    }

    private function updateXmlString(string $xpath, string $xml_string, int $uid): string
    {
        $xml = simplexml_load_string($xml_string);
        if ($xml === false) {
            echo sprintf('Failed to load XML: %s%s', $xml, PHP_EOL);

            return '';
        }

        $elements = $xml->xpath($xpath);
        if (count($elements) == 0) {
            return '';
        }

        $string_changed = false;
        $debug_string = "\nuid: " . $uid . " -- Xpath: " . $xpath . "\n";

        /** @var \SimpleXMLElement $element */
        foreach ($elements as $element) {
            // Check if the value contains only digits
            if (preg_match('/^\d+$/', (string)$element)) {
                // Replace the number with the desired format
                $org = (string)$element;
                $element[0] = 't3://file?uid=' . (string)$element;
                $debug_string .= " - Replaced: " . $org . " with " . (string)$element . "\n";
                $string_changed = true;
                $this->counter++;
            }
        }

        if ($string_changed) {
            print_r($debug_string);

            return (string)$xml->asXML();
        }

        return '';
    }
}
