<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for tt_news
 */
class NewsRepository extends Repository
{
    /**
     * @return array<object>|QueryResultInterface
     */
    public function getNewsByFolder(int $newsProductLocationsFolder, array $newsIDS)
    {
        $query = $this->createQuery();
        //$query->statement('SELECT * FROM tt_news where hidden = 0 and deleted = 0 and pid =' . $newsProductLocationsFolder . ' and uid in(' . $newsIDS . ')');

        $query->matching(
            $query->logicalAnd([
                $query->equals('hidden', 0),
                $query->equals('deleted', 0),
                $query->equals('pid', $newsProductLocationsFolder),
                $query->in('uid', $newsIDS),
            ]),
        );

        return $query->execute(true);
    }
}
