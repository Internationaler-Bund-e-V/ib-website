<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Command;

use DOMDocument;
use Rector\Naming\Matcher\ForeachMatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertProcessedImagePathsCommand extends AbstractCommand
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    protected function configure()
    {
        parent::configure();
        $this->setDescription('convert rte_ckeditor processed image paths to real file paths');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $this->processPiflexform();
        $this->processBodytext();

        return 0; // return 0 (SUCCESS) or 1 (FAILURE).
    }

    private function processBodytext(): void
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder_ttcontent = $pool->getConnectionForTable('tt_content')->createQueryBuilder();

        $queryBuilder_ttcontent
            ->getRestrictions()
            ->removeAll();

        $statement_ttcontent = $queryBuilder_ttcontent
            ->select('uid', 'bodytext', 'list_type', 'pid')
            ->from('tt_content')
            ->executeQuery();

        $all_results = $statement_ttcontent->fetchAllAssociative();

        foreach ($all_results as $row) {
            if ($row['uid'] != 21915) {
                //continue;
            }

            if (!$row['bodytext']) {
                continue;
            }

            // Use DOMDocument to parse the HTML content
            $dom = new DOMDocument();

            // phpcs:ignore
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $row['bodytext']); // Suppress warnings for malformed HTML

            if (strpos($row['bodytext'], '_processed_') === false) {
                continue;
            }

            $processed_html = $this->replaceProcessedPaths($dom);
            if ($processed_html != "" && (int) $row['uid'] > 0) {
                #print_r($processed_html);

                $queryBuilder_ttcontent_update = $pool->getConnectionForTable('tt_content')->createQueryBuilder();
                $queryBuilder_ttcontent_update
                    ->update('tt_content')
                    ->where(
                        $queryBuilder_ttcontent->expr()->eq('uid', $row['uid'])
                    )->set('bodytext', (string) $processed_html)->executeStatement();
            }
        }
    }

    private function processPiflexform(): void
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder_ttcontent = $pool->getConnectionForTable('tt_content')->createQueryBuilder();

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
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_bubbleslider')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_accordion')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_mediaelement')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_tiles')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_textextended')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_contentslider')
                    ),
                    $queryBuilder_ttcontent->expr()->eq(
                        'list_type',
                        $queryBuilder_ttcontent->createNamedParameter('ibcontent_sidebardownloads')
                    ),
                )
            )
            //->where($queryBuilder_ttcontent->expr()->eq('uid', 7386))
            //->setMaxResults(1)
            ->executeQuery();

        $all_results = $statement_ttcontent->fetchAllAssociative();

        foreach ($all_results as $row) {
            if ($row['uid'] != 21918) {
                //continue;
            }

            $xml = simplexml_load_string((string) $row['pi_flexform']);
            if ($xml) {
                $values = $xml->xpath('//value[@index="vDEF"]');

                $write_to_db = false;
                foreach ($values as $value) {
                    // Convert the <value> node to a string to get its content
                    $htmlContent = trim((string) $value);
                    if ($htmlContent == '') {
                        continue;
                    }

                    // Use DOMDocument to parse the HTML content
                    $dom = new DOMDocument();

                    // phpcs:ignore
                    @$dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent); // Suppress warnings for malformed HTML

                    #print_r(" ---- " . $htmlContent . " ---- ");
                    if (strpos($htmlContent, '_processed_') === false) {
                        continue;
                    }

                    $write_to_db = true;

                    $processed_html = $this->replaceProcessedPaths($dom);
                    if ($processed_html != "") {
                        // @phpstan-ignore-next-line
                        $value[0] = $processed_html;
                    }
                }

                // Output the modified XML
                #print_r((string)$xml->asXML());
                #$write_to_db = false;
                if ($write_to_db && (int) $row['uid'] > 0) {
                    $queryBuilder_ttcontent_update = $pool->getConnectionForTable('tt_content')->createQueryBuilder();
                    $queryBuilder_ttcontent_update
                        ->update('tt_content')
                        ->where(
                            $queryBuilder_ttcontent->expr()->eq('uid', $row['uid'])
                        )->set('pi_flexform', (string) $xml->asXML())->executeStatement();
                }
            } else {
                print_r($row['uid'] . ' -- no xml');
            }
        }
    }

    private function replaceProcessedPaths(DOMDocument $dom): string
    {
        $update_xml_tag = false;

        /** @var \DOMElement $imgTag */
        foreach ($dom->getElementsByTagName('img') as $imgTag) {
            $file_uid = $imgTag->getAttribute('data-htmlarea-file-uid');
            $src = $imgTag->getAttribute('src');

            if (!$file_uid) {
                continue;
            }

            try {
                /** @var File $file */
                $file = $this->resourceFactory->getFileObject((int) $file_uid);
                $public_url = $file->getPublicUrl();
                $update_xml_tag = true;
            } catch (FileDoesNotExistException $e) {
                $public_url = '';
            }

            // add leading "/" if not present
            if ($public_url && substr($public_url, 0, 1) !== '/') {
                $public_url = '/' . $public_url;
            }

            // Set a new src value - you can set it to any new value as needed
            //var_dump($src . ' - ' . $public_url);
            if ($public_url) {
                $imgTag->setAttribute('src', $public_url);
            }
        }

        // Save the modified HTML
        $newHtmlContent = '';
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $childNode) {
            $newHtmlContent .= $dom->saveHTML($childNode);
        }

        // Update the <value> node with the modified HTML content
        $to_return = "";
        if ($update_xml_tag) {
            $to_return = $newHtmlContent;
        }

        return $to_return;
    }
}
