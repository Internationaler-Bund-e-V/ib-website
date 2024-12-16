<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Updates;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class PluginPermissionUpdaterDBJob implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'txIBContentPluginPermissionUpdaterDBJob';
    }

    public function getTitle(): string
    {
        return 'EXT:ib_content: Migrate plugin permissions';
    }

    public function getDescription(): string
    {
        $description = 'This update wizard updates all permissions and allows **all** ib_content (db jobs) plugins instead of the previous single plugin.';
        $description .= ' Count of affected be_groups: ' . count($this->getMigrationRecords());

        return $description;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        return $this->checkIfWizardIsRequired();
    }

    public function executeUpdate(): bool
    {
        return $this->performMigration();
    }

    public function checkIfWizardIsRequired(): bool
    {
        return $this->getMigrationRecords() !== [];
    }

    public function performMigration(): bool
    {
        $records = $this->getMigrationRecords();

        foreach ($records as $record) {
            $this->updateRow($record);
        }

        return true;
    }

    protected function getMigrationRecords(): array
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        /** @var DeletedRestriction $deletedRestriction */
        $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);

        $queryBuilder = $connectionPool->getQueryBuilderForTable('be_groups');
        $queryBuilder->getRestrictions()->removeAll()->add($deletedRestriction);

        return $queryBuilder
            ->select('uid', 'explicit_allowdeny')
            ->from('be_groups')
            ->where(
                $queryBuilder->expr()->like(
                    'explicit_allowdeny',
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('tt_content:list_type:ibcontent_dbjobmodul') . '%')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function updateRow(array $row): void
    {
        $default = 'tt_content:list_type:ibcontent_dbjobmodul,tt_content:list_type:ibcontent_dbjobmodulshowjoblist,tt_content:list_type:ibcontent_dbjobmodulshowjob,tt_content:list_type:ibcontent_dbjobmodulshowforeignjob';

        /** @var Typo3Version $t3version */
        $t3version = GeneralUtility::makeInstance(Typo3Version::class);

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        if ($t3version->getMajorVersion() >= 12) {
            $searchReplace = [
                'tt_content:list_type:ibcontent_dbjobmodul:ALLOW' => $default,
                'tt_content:list_type:ibcontent_dbjobmodul:DENY' => '',
                'tt_content:list_type:ibcontent_dbjobmodul' => $default,
            ];
        } else {
            $default .= ',';
            $default = str_replace(',', ':ALLOW,', $default);
            $searchReplace = [
                'tt_content:list_type:ibcontent_dbjobmodul:ALLOW' => $default,
                'tt_content:list_type:ibcontent_dbjobmodul:DENY' => str_replace($default, 'ALLOW', 'DENY'),
            ];
        }

        $newList = str_replace(array_keys($searchReplace), array_values($searchReplace), $row['explicit_allowdeny']);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('be_groups');
        $queryBuilder->update('be_groups')
            ->set('explicit_allowdeny', $newList)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($row['uid'], Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }
}
