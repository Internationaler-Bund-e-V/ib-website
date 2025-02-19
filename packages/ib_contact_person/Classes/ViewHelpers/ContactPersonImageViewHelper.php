<?php

declare(strict_types=1);

namespace Ib\IbContactPerson\ViewHelpers;

use Ib\IbContactPerson\Domain\Repository\ContactPersonRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ContactPersonImageViewHelper extends AbstractViewHelper
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

        #DebuggerUtility::var_dump($person);

        /*
        $queryBuilderSysFile = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
        $sys_file = $queryBuilderSysFile
            ->select('*')
            ->from('sys_file')
            ->join(
                'sys_file',
                'sys_file_reference',
                'sfr',
                $queryBuilderSysFile->expr()->eq('sfr.uid_local', $queryBuilderSysFile->quoteIdentifier('sys_file.uid'))
            )
            ->where(
                $queryBuilderSysFile->expr()->eq(
                    'sfr.tablenames',
                    $queryBuilderSysFile->createNamedParameter('tx_ibcontactperson_domain_model_contactperson')
                ),
                $queryBuilderSysFile->expr()->eq(
                    'sfr.deleted',
                    0
                ),
                $queryBuilderSysFile->expr()->eq(
                    'sfr.uid_foreign',
                    $queryBuilderSysFile->createNamedParameter($this->arguments['personid'], \PDO::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
            */

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilderSysFile = $pool->getQueryBuilderForTable('sys_file_reference');
        $sfr = $queryBuilderSysFile
            ->select('*')
            ->from('sys_file_reference')
            ->where(
                $queryBuilderSysFile->expr()->eq(
                    'sys_file_reference.tablenames',
                    $queryBuilderSysFile->createNamedParameter('tx_ibcontactperson_domain_model_contactperson')
                ),
                $queryBuilderSysFile->expr()->eq(
                    'sys_file_reference.deleted',
                    0
                ),
                $queryBuilderSysFile->expr()->eq(
                    'sys_file_reference.uid_foreign',
                    $queryBuilderSysFile->createNamedParameter($personid, \PDO::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        if ($sfr !== false && isset($sfr['uid'])) {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $xx = $resourceFactory->getFileReferenceObject($sfr['uid']);

            if ($xx->getPublicUrl()) {
                return $xx->getPublicUrl();
            }
        }

        return "";
    }
}
