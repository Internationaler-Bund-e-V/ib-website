<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Domain\Repository;

use Ib\Ibcontent\Domain\Model\News;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<News>
 */
class NewsRepository extends Repository
{
    /**
     * Gibt eine Liste von News-Objekten zurück, die sich in einem bestimmten Ordner befinden und eine der angegebenen IDs haben.
     *
     * @param int $newsProductLocationsFolder Die PID des Ordners, in dem nach News gesucht wird.
     * @param int[] $newsIDS Die Liste von News-IDs, nach denen gefiltert wird.
     * @return QueryResultInterface<int, News> Ein QueryResultInterface mit den News-Objekten.
     */
    public function getNewsByFolder(int $newsProductLocationsFolder, array $newsIDS): QueryResultInterface
    {
        $query = $this->createQuery();

        $constraint = $query->logicalAnd(
            $query->equals('hidden', 0),
            $query->equals('deleted', 0),
            $query->equals('pid', $newsProductLocationsFolder),
            $query->in('uid', $newsIDS)
        );

        $query->matching($constraint);

        return $query->execute();
    }
}
