<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Domain\Repository;

use Rms\IbFormbuilder\Domain\Model\Emaildata;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
 * @extends Repository<Emaildata>
 */
class EmaildataRepository extends Repository
{
    /** @var array<non-empty-string, QueryInterface::ORDER_*> */
    protected $defaultOrderings = array(
        'sorting' => QueryInterface::ORDER_ASCENDING,
    );

    /**
     * @throws IllegalObjectTypeException
     * @param int<0, max> $pid
     */
    public function saveFormData(string $htmlContent, string $formName, int $pid, int $formId, bool $errorOnSend): void
    {
        // convert bool to in (required for serErrorOnSend())
        $error = 1;
        if (!$errorOnSend) {
            $error = 0;
        }

        $newItem = new Emaildata();
        $newItem->setEmailDataHtml($htmlContent);
        //$newItem->setEmailDataCsv("x;y");
        $newItem->setName($formName);
        $newItem->setPid($pid);
        $newItem->setRelatedFormId($formId);
        $newItem->setErrorOnSend($error);
        $this->add($newItem);
    }

    public function findByRelatedFormId(int $formId): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('relatedFormId', $formId)
        );

        return $query->execute()->toArray();
    }
}
