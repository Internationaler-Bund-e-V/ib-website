<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class CleanFlexformDataCommand extends AbstractCommand
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('clean data in flexform fields');
    }

    /**
     * search for a string in tt_content - pi_flexform field and replace it with another string
     * used to make relative paths absolute after Update from TYPO3 v10 -> v11
     * mk@rms, 2022-07-18
     *
     * @param string $search_string
     * @param string $search
     * @param string $replace
     * @param string $targetColumn I.e. pi_flexform or bodytext
     * @return void
     */
    private function replace($search_string, $search, $replace, $targetColumn = 'pi_flexform')
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder_ttcontent = $pool->getConnectionForTable('tt_content')->createQueryBuilder();

        //$search_string = '"fileadmin/user_upload/';
        $statement_ttcontent = $queryBuilder_ttcontent
            ->select('uid', $targetColumn, 'list_type', 'pid')
            ->from('tt_content')
            ->where(
                $queryBuilder_ttcontent->expr()->like(
                    $targetColumn,
                    $queryBuilder_ttcontent->createNamedParameter('%' . $queryBuilder_ttcontent->escapeLikeWildcards($search_string) . '%')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($statement_ttcontent as $row) {
            $org = $row[$targetColumn];
            $new = str_replace($search, $replace, (string)$org);

            $queryBuilder_ttcontent
                ->update('tt_content')
                ->where(
                    $queryBuilder_ttcontent->expr()->eq('uid', $row['uid'])
                )->set($targetColumn, $new)->executeStatement();

            print_r("\nreplace " . $row['uid'] . "\n");
        }
    }

    /**
     * After Update to TYPO3 11:
     * In flexoform textfields are links to images without a leading slash,
     * i.e src="fileadmin/user_upload/storage_ib_redaktion/IB_Kongress/Icons/microphone_Flaticon.png"
     * This command adds a leading slash to this images
     *
     * result: src="/fileadmin/user_upload/storage_ib_redaktion/IB_Kongress/Icons/microphone_Flaticon.png"
     *
     * mk@rms, 2022-07-14
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        print_r("\n\n" . " START cleaning up \n\n");

        // select uid, pi_flexform from tt_content where pi_flexform like '%"fileadmin/user_upload/%' and deleted=0;
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder_ttcontent = $pool->getConnectionForTable('tt_content')->createQueryBuilder();
        $queryBuilder_ttcontent->getRestrictions()->removeAll();

        // ---------------------------------
        // replace variant one
        // ---------------------------------
        $target_columns = ['pi_flexform', 'bodytext', 'tx_mask_srb_text_1', 'tx_mask_srb_text_2', 'tx_mask_srb_text_3'];
        foreach ($target_columns as $tcol) {
            $this->replace('"fileadmin/user_upload/', 'src="fileadmin/user_upload/', 'src="/fileadmin/user_upload/', $tcol);
            $this->replace('src=&quot;fileadmin/user_upload/', 'src=&quot;fileadmin/user_upload/', 'src=&quot;/fileadmin/user_upload/', $tcol);
            $this->replace('src=&quot;fileadmin/_processed_/', 'src=&quot;fileadmin/_processed_/', 'src=&quot;/fileadmin/_processed_/', $tcol);
        }

        print_r("\n\n" . " END cleaning up \n\n");

        return 0; // return 0 (SUCCESS) or 1 (FAILURE).
    }
}
