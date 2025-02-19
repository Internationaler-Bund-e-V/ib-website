<?php

declare(strict_types=1);

namespace Ib\IbContactPerson\ViewHelpers;

use Ib\IbContactPerson\Domain\Repository\ContactPersonRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class ContactPersonNameViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var ContactPersonRepository
     */
    protected $contactPersonRepository;

    public function injectContactPersonRepository(ContactPersonRepository $contactPersonRepository): void
    {
        $this->contactPersonRepository = $contactPersonRepository;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('personid', 'int', 'ID of the contact person', true);
    }

    /**
     * Parse a content element
     */
    public function render(): string
    {
        $personid = (int)$this->arguments['personid'];

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilderContactPerson = $pool->getQueryBuilderForTable('tx_ibcontactperson_domain_model_contactperson');
        $queryBuilderContactPerson->getRestrictions()->removeAll();
        $person = $queryBuilderContactPerson
            ->select('*')
            ->from('tx_ibcontactperson_domain_model_contactperson')
            ->where(
                $queryBuilderContactPerson->expr()->eq(
                    'uid',
                    $queryBuilderContactPerson->createNamedParameter($personid, \PDO::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        //$product = $this->contactPersonRepository->findByUid($this->arguments['personid']);
        //return $product->getName();
        //DebuggerUtility::var_dump($person);
        if (isset($person['name'])) {
            return trim((string)$person['name']);
        }

        return "";
    }
}
