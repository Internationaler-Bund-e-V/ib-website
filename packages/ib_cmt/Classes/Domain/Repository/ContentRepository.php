<?php

declare(strict_types=1);

namespace IB\IbCmt\Domain\Repository;

//use Doctrine\DBAL\Driver\DrizzlePDOMySql\Connection;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ContentRepository extends Repository
{
    // Order by BE sorting
    protected $defaultOrderings = array(
        'allowed' => QueryInterface::ORDER_ASCENDING,
    );

    public function getContentTimestamp(int $uid): array
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $pool->getConnectionForTable('tt_content');

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $query = $queryBuilder
            ->select('tstamp')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            );

        //$rows = $query->execute()->fetchColumn(0);
        $rows = $query->executeQuery()->fetchFirstColumn();

        return $rows;
    }

    /**
     * typo3content
     *
     * @return QueryResultInterface|object[]
     */
    public function getTypo3Content()
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            [
                $query->equals('contenttype', 0),
                $query->equals('allowed', 0),
            ]
        ));

        return $query->execute();
    }

    /**
     * redaktionstool
     *
     * @return QueryResultInterface|object[]
     */
    public function getRtContent()
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            [
                $query->equals('contenttype', 1),
                $query->equals('allowed', 0),
            ]
        ));

        return $query->execute();
    }

    /**
     * news content
     *
     * @return QueryResultInterface|object[]
     */
    public function getNewsContent()
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            [
                $query->equals('contenttype', 2),
                $query->equals('allowed', 0),
            ]
        ));

        return $query->execute();
    }

    public function cleanContent(array $keepContents): void
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $pool->getConnectionForTable('tx_ibcmt_domain_model_content');

        $queryBuilder = $connection->createQueryBuilder();
        $query = $queryBuilder
            ->delete('tx_ibcmt_domain_model_content')
            ->where(
                $queryBuilder->expr()->notIn('uid', $queryBuilder->createNamedParameter($keepContents, Connection::PARAM_INT_ARRAY))
            );

        $query->executeStatement();
    }

    public function findByContenttype(int $contenttype): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('contenttype', $contenttype));

        return $query->execute()->toArray();
    }

    public function findByContentid(string $contentid): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('contentid', $contentid));

        return $query->execute()->toArray();
    }
}
