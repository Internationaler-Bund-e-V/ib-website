<?php

declare(strict_types=1);

namespace Rms\IbContactPerson\ViewHelpers;

use Rms\IbContactPerson\Domain\Repository\ContactPersonRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ContactPersonPositionViewHelper extends AbstractViewHelper
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
        $this->registerArgument('personid', 'int', 'The email address to resolve the gravatar for', true);
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

        //DebuggerUtility::var_dump($person);
        $position = "";
        if (isset($person['position']) && trim((string)$person['position'])) {
            $position = '<br/>' . $person['position'];
        }

        return $position;

        //$contact = $this->contactPersonRepository->findByUid($this->arguments['personid']);
        //$position = $contact->getPosition();
        //if (trim($position)) {
        //  $position = '<br>' . $position;
        //}
        //return $position;
    }
}
