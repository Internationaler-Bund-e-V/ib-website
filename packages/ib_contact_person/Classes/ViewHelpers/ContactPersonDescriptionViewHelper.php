<?php

declare(strict_types=1);

namespace Ib\IbContactPerson\ViewHelpers;

use Ib\IbContactPerson\Domain\Repository\ContactPersonRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ContactPersonDescriptionViewHelper extends AbstractViewHelper
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
     *
     * @return string Parsed Content Element
     */
    public function render()
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

        if (isset($person['contact_info'])) {
            return $person['contact_info'];
        }

        return "";

        //$product = $this->contactPersonRepository->findByUid($this->arguments['personid']);
        //return $product->getContactInfo();
    }
}
