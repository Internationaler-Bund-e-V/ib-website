<?php

declare(strict_types=1);

namespace Ib\IbFormbuilder\Domain\Repository;

use Ib\IbFormbuilder\Domain\Model\Form;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/***
 *
 * This file is part of the "IB Formbuilder" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Michael Kettel <mkettel@gmail.com>, rms. relationship marketing solutions GmbH
 *
 ***/

/**
 * @extends Repository<Form>
 */
class FormRepository extends Repository
{
    /** @var array<non-empty-string, QueryInterface::ORDER_*> */
    protected $defaultOrderings = array(
        'sorting' => QueryInterface::ORDER_ASCENDING,
    );

    public function initializeObject(): void
    {
    }

    /**
     * get forms for a given pid, only returns the fields needed for a flexform select list
     *
     * @see Configuration/FlexForms/add_form.xml
     * @see Classes/UserFunc/FlexFormUserFunc.php
     *
     * @param $pidList
     * @return array<mixed,mixed>
     */
    public function getFormsForFlexform(string $pidList): array
    {
        $result = [];
        $result[] = [
            'name' => 'Bitte einen Ordner wählen',
            'uid' => -1,
        ];
        if ($pidList !== '') {

            /** @var ConnectionPool $pool */
            $pool = GeneralUtility::makeInstance(ConnectionPool::class);
            $queryBuilder = $pool->getQueryBuilderForTable('tx_ibformbuilder_domain_model_form');
            $result = $queryBuilder
                ->select('uid', 'name')
                ->from('tx_ibformbuilder_domain_model_form')
                ->where(
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->in('pid', $pidList),
                )->executeQuery()
                ->fetchAllAssociative();
        }

        return $result;
    }
}
