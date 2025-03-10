<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Updates;

use GeorgRinger\News\Event\PluginUpdaterListTypeEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class PluginUpdaterDBJob implements UpgradeWizardInterface
{
    private string $list_type_org = 'ibcontent_dbjobmodul';
    private const MIGRATION_SETTINGS = [
        [
            'switchableControllerActions' => 'MyContent->dbShowJoblist',
            'targetListType' => 'ibcontent_dbjobmodulshowjoblist',
        ],
        [
            'switchableControllerActions' => 'MyContent->dbShowJob',
            'targetListType' => 'ibcontent_dbjobmodulshowjob',
        ],
        [
            'switchableControllerActions' => 'MyContent->dbShowForeignjob',
            'targetListType' => 'ibcontent_dbjobmodulshowforeignjob',
        ],
    ];

    /** @var FlexFormService */
    protected $flexFormService;

    /** @var FlexFormTools */
    protected $flexFormTools;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct()
    {
        /** @var FlexFormService $flexFormService */
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $this->flexFormService = $flexFormService;

        /** @var FlexFormTools $flexFormTools */
        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        $this->flexFormTools = $flexFormTools;

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getIdentifier(): string
    {
        return 'txIBContentPluginUpdaterJob';
    }

    public function getTitle(): string
    {
        return 'EXT:ib_content->DBJobModul: Migrate switchable actions to plugins';
    }

    public function getDescription(): string
    {
        $description = 'Migrate switchable actions for list_type ' . $this->list_type_org . ' to new plugins. ';
        $description .= 'The new plugins are: ' . implode(', ', array_column(self::MIGRATION_SETTINGS, 'targetListType')) . ' ';
        $description .= 'Count of plugins: ' . count($this->getMigrationRecords());

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

        /** @var LanguageServiceFactory $languageServiceFactory */
        $languageServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);

        // Initialize the global $LANG object if it does not exist.
        // This is needed by the ext:form flexforms hook in Core v11
        $GLOBALS['LANG'] = $GLOBALS['LANG'] ?? $languageServiceFactory->create('default');

        foreach ($records as $record) {
            $flexForm = $this->flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
            $targetListType = $this->getTargetListType($flexForm['switchableControllerActions'] ?? '');
            /** @var PluginUpdaterListTypeEvent $event */
            $event = $this->eventDispatcher->dispatch(new PluginUpdaterListTypeEvent($flexForm, $record, $targetListType));
            $targetListType = $event->getListType();

            if ($targetListType === '') {
                continue;
            }

            // Update record with migrated types (this is needed because FlexFormTools
            // looks up those values in the given record and assumes they're up-to-date)
            $record['CType'] = $targetListType;
            $record['list_type'] = '';

            // Clean up flexform
            $newFlexform = $this->flexFormTools->cleanFlexFormXML('tt_content', 'pi_flexform', $record);
            //$flexFormData = GeneralUtility::xml2array($newFlexform);
            $flexFormData = GeneralUtility::xml2array($record['pi_flexform']);

            // Remove flexform data which do not exist in flexform of new plugin
            if (isset($flexFormData['data'])) {
                foreach ($flexFormData['data'] as $sheetKey => $sheetData) {
                    // Remove empty sheets
                    if (!count($flexFormData['data'][$sheetKey]['lDEF']) > 0) {
                        unset($flexFormData['data'][$sheetKey]);
                    }
                }
            }

            if (isset($flexFormData['data']) && $flexFormData['data'] !== []) {
                $newFlexform = $this->array2xml($flexFormData);
            } else {
                $newFlexform = '';
            }

            $this->updateContentElement($record['uid'], $targetListType, $newFlexform);
        }

        return true;
    }

    protected function getMigrationRecords(): array
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        /** @var \TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction $restriction */
        $restriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder->getRestrictions()->removeAll()->add($restriction);

        return $queryBuilder
            ->select('uid', 'pid', 'CType', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('list')
                ),
                $queryBuilder->expr()->eq(
                    'list_type',
                    //$queryBuilder->createNamedParameter('news_pi1')
                    //$queryBuilder->createNamedParameter('drwcontent_drwcontacts')
                    $queryBuilder->createNamedParameter($this->list_type_org)
                )
            )
            ->orderBy('uid', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function getTargetListType(string $switchableControllerActions): string
    {
        foreach (self::MIGRATION_SETTINGS as $setting) {
            if ($setting['switchableControllerActions'] === $switchableControllerActions) {
                return $setting['targetListType'];
            }
        }

        return '';
    }

    /**
     * Updates list_type and pi_flexform of the given content element UID
     *
     * @param int $uid
     * @param string $targetListType
     * @param string $flexform
     */
    protected function updateContentElement(int $uid, string $targetListType, string $flexform): void
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $pool->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('CType', 'list')
            ->set('list_type', $targetListType)
            ->set('pi_flexform', $flexform)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    /**
     * Transforms the given array to FlexForm XML
     *
     * @param array $input
     * @return string
     */
    protected function array2xml(array $input = []): string
    {
        $options = [
            'parentTagMap' => [
                'data' => 'sheet',
                'sheet' => 'language',
                'language' => 'field',
                'el' => 'field',
                'field' => 'value',
                'field:el' => 'el',
                'el:_IS_NUM' => 'section',
                'section' => 'itemType',
            ],
            'disableTypeAttrib' => 2,
        ];
        $spaceInd = 4;
        $output = GeneralUtility::array2xml($input, '', 0, 'T3FlexForms', $spaceInd, $options);
        $output = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . LF . $output;

        return $output;
    }
}
