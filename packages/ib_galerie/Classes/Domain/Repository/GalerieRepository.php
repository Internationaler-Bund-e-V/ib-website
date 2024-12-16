<?php

declare(strict_types=1);

namespace Rms\IbGalerie\Domain\Repository;

use Rms\IbGalerie\Domain\Model\Galerie;
use TYPO3\CMS\Extbase\Persistence\Repository;

/***
 *
 * This file is part of the "ibgalerie" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018
 *
 ***/

/**
 * The repository for Galeries
 */
class GalerieRepository extends Repository
{
    public function findByCode(string $code): ?Galerie
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('code', $code)
        );

        /** @var Galerie $glr */
        $glr = $query->execute()->getFirst();

        return $glr;
    }
}
