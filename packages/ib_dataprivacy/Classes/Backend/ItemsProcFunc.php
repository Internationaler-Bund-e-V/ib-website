<?php

declare(strict_types=1);

namespace Rms\IbDataprivacy\Backend;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemsProcFunc
{
    public string $table = 'tt_content';
    protected QueryBuilder $queryBuilder;

    public function __construct()
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->queryBuilder = $pool->getQueryBuilderForTable('tt_content');
    }

    public function user_getContentelements(array &$config): void // phpcs:ignore
    {
        //get flexforms 'view' variable
        $flexformView = $config['field'];

        $pid = $config['flexParentDatabaseRow']['pid'];
        $pagesTSconfig = BackendUtility::getPagesTSconfig($pid);
        $ibTSconfig = $pagesTSconfig['TCEFORM.']['tt_content.']['pi_flexform.']['ibdataprivacy_dataprivacy.'];

        $pageIds = array();
        $recursive = false;

        // values of $ibTSconfig see typo3/public/typo3conf/ext/ib_dataprivacy/Configuration/TSconfig/tsConfig.tsconfig
        switch ($flexformView) {
            //Impressum
            case 'settings.view1.imprint':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_IMPRESSUM']);
                break;
            case 'settings.view1.options':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_IMPRESSUM_OPTIONS']);
                $recursive = true;
                break;
            //Datenschutzerklärung
            case 'settings.view2.dataprivacy':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_DATENSCHUTZERKLAERUNG']);
                break;
            case 'settings.view2.options':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_DATENSCHUTZERKLAERUNG_OPTIONS']);
                $recursive = true;
                break;
            //Datenschutzerklärung Drittplattformen
            case 'settings.view3.dataprivacyplattform':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_DATENSCHUTZERKLAERUNG_DRITTE']);
                break;
            case 'settings.view3.options':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_DATENSCHUTZERKLAERUNG__DRITTE_OPTIONS']);
                $recursive = true;
                break;
            //Barrierefreiheit
            case 'settings.view4.barrierefreiheit':
                $pageIds = explode(',', (string) $ibTSconfig['ID_LIST_BARRIEREFREIHEIT']);
                $recursive = true;
                break;

            default:
                # code...
                break;
        }

        //get all pids
        if ($recursive) {
            $recPageIds = array();
            foreach ($pageIds as $key => $value) {
                $treeids = $this->getTreePids((int) $value);
                //\debug($treeids);
                $recPageIds = array_merge($recPageIds, $treeids);
            }
            $pageIds = $recPageIds;
        }

        $statement = $this->queryBuilder
            ->select('uid', 'header', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $this->queryBuilder->expr()->in('pid', $this->queryBuilder->createNamedParameter($pageIds, Connection::PARAM_INT_ARRAY)),
                $this->queryBuilder->expr()->eq('sys_language_uid', $this->queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            )
            ->executeQuery()->fetchAllAssociative();

        $sys_language_uid = $config['flexParentDatabaseRow']['sys_language_uid'];

        //declare return array for items
        $config['items'] = array();

        //generate items
        foreach ($statement as $item) {
            //check if translation for the current language exists
            $count = $this->queryBuilder->count('uid')->from('tt_content')
                ->where(
                    $this->queryBuilder->expr()->eq('l10n_source', $this->queryBuilder->createNamedParameter($item['uid'], \PDO::PARAM_INT)),
                    $this->queryBuilder->expr()->eq('sys_language_uid', $this->queryBuilder->createNamedParameter($sys_language_uid, \PDO::PARAM_INT)),
                )->executeQuery()->fetchFirstColumn();

            //\debug($count);

            //only add tranlated items
            if ($count !== [] || $sys_language_uid == 0) {
                $flexFormArray = GeneralUtility::xml2array($item['pi_flexform']);
                $flexHeadline = $item['header'] . " - ";
                if (isset($flexFormArray['data']['sDEF']['lDEF']['settings.array']['el'])) {
                    $flexItem = $flexFormArray['data']['sDEF']['lDEF']['settings.array']['el'];
                    foreach ($flexItem as $slide) {
                        $flexHeadline .= $slide['ItemWrap']['el']['headline']['vDEF'];
                    }
                }

                $config['items'][] = [$flexHeadline, $item['uid']];
            }
        }
    }

    public function getTreePids(int $parent = 0, bool $as_array = true): array
    {
        $depth = 999999;
        //$queryGenerator = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\QueryGenerator');

        /** @var QueryGenerator $queryGenerator */
        $queryGenerator = GeneralUtility::makeInstance(QueryGenerator::class);
        $childPids = $queryGenerator->getTreeList($parent, $depth, 0, "1"); //Will be a string like 1,2,3
        if ($as_array) {
            $childPids = explode(',', $childPids);
        }

        if (!\is_array($childPids)) {
            $childPids = [];
        }

        return $childPids;
    }
}
