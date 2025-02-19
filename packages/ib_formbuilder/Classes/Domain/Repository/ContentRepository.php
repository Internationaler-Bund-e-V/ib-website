<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Domain\Repository;

use Rms\IbFormbuilder\Domain\Model\Content;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Content>
 */
class ContentRepository extends Repository
{
    /**
     * this function gets the receivers that are configured in the flexform of the contentelement
     * for the given uid
     * It returns an array with all the email adresses
     *
     * @author mk, 2017-07-19
     * @see Configuration/FlexForms/add_form.xml
     */
    public function getFlexformForContentUid(int $uid): array
    {
        $uid = (int)$uid;

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $pool->getQueryBuilderForTable('tt_content');
        $result = $queryBuilder
            ->select('uid', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            )->executeQuery()
            ->fetchAllAssociative();

        //$query = $this->createQuery();
        //$query->statement('SELECT uid, pi_flexform FROM tt_content where hidden = 0 and deleted = 0 and uid = ' . $uid);
        //$result = $query->execute(true);

        $toReturn = [
            'receivers' => [],
            'saveToDatabase' => false,
            'saveToDatabaseName' => 'Emaildaten',
        ];
        // parse the flexform xml and create an array with the email adresses
        /** @var FlexFormService $ffs */
        $ffs = GeneralUtility::makeInstance(FlexFormService::class);
        $flex = $ffs->convertFlexFormContentToArray($result[0]['pi_flexform']);

        // read receivers
        $receivers = $flex['settings']['receiver'];
        $receiversArray = array_map('trim', explode(',', (string)$receivers));

        // check if formdata should be saved into database
        $saveToDatabase = $flex['settings']['saveDataToDb'];
        $saveToDatabaseName = $flex['settings']['savedDataName'];

        $toReturn['receivers'] = $receiversArray;
        $toReturn['saveToDatabase'] = $saveToDatabase;
        $toReturn['saveToDatabaseName'] = $saveToDatabaseName;

        return $toReturn;
    }
}
