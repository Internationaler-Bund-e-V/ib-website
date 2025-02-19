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
 * @extends Repository<Galerie>
 */
class GalerieRepository extends Repository
{
    /**
     * Find a Galerie by its code
     *
     * @param string $code
     * @return Galerie|null
     */
    public function findByCode(string $code): ?Galerie
    {
        // Erstellen des Abfrage-Objekts
        $query = $this->createQuery();

        // Matching der Abfrage (Vergleich des "code"-Feldes)
        $query->matching(
            $query->equals('code', $code)
        );

        // Ausführen der Abfrage und das erste Ergebnis zurückgeben
        /** @var Galerie|null $galerie */
        $galerie = $query->execute()->getFirst();

        return $galerie;
    }
}
