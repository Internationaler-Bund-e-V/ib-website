<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\UserFunc;

use Rms\IbFormbuilder\Domain\Repository\FormRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class FlexFormUserFunc
 */
class FlexFormUserFunc
{
    private array $extbaseFrameworkConfiguration;

    protected FormRepository $formRepository;

    public function __construct()
    {
    }

    /**
     * @param array $fConfig
     *
     * @return void
     */
    public function getForms(&$fConfig)
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);

        // extract the list of items inside the "Record Storage Page"
        $pidList = $this->getConfiguredPagesFromPlugin($fConfig);
        //debug($fConfig);

        // Laden der Abhängigkeit, z. B. eine Klasse, die du benötigst
        $this->formRepository = GeneralUtility::makeInstance(FormRepository::class);

        //if record storage is not set via plugin data records, try to read module ts config
        if (empty($pidList)) {
            //$this->configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
            $this->extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            );

            $pidList = (string) $this->extbaseFrameworkConfiguration['module.']['tx_ibformbuilder_web_ibformbuilderibforms.']['persistence.']['storagePid'];
        }

        // Get data from repository
        $myData = $this->formRepository
            ->getFormsForFlexform($pidList);

        // create array with selectable items
        foreach ($myData as $data) {
            array_push($fConfig['items'], array(
                $data['name'],
                $data['uid'],
            ));
        }
    }

    /**
     * Get configured pages from "pages" attribute in plugin's row
     * TYPO3 7.6 and 8.7 have different types in $config['flexParentDatabaseRow']['pages'].
     *
     * This method handles both.
     *
     * @see https://github.com/teaminmedias-pluswerk/ke_search/blob/master/Classes/Backend/class.user_filterlist.php
     *
     * @param array $config
     * @return string
     */
    protected function getConfiguredPagesFromPlugin(&$config)
    {
        // check if the tt_content row is available and if not load it manually
        // flexParentDatabaseRow not set can be caused by compatibility6 extension
        if (
            !isset($config['flexParentDatabaseRow']) || (isset($config['flexParentDatabaseRow']) && !is_array($config['flexParentDatabaseRow']))
        ) {
            $parentRow = BackendUtility::getRecord(
                'tt_content',
                $config['row']['uid']
            );
            if (is_array($parentRow)) {
                $config['flexParentDatabaseRow'] = $parentRow;
            } else {
                // tt_content row not found
                return '';
            }
        }
        $parentRow = (array) $config['flexParentDatabaseRow'];
        $pages = $parentRow['pages'];
        $pids = [];
        if (is_string($pages)) {
            // TYPO3 7.6
            $pagesParts = GeneralUtility::trimExplode(',', $pages, true);
            foreach ($pagesParts as $pagePart) {
                $a = GeneralUtility::trimExplode('|', $pagePart);
                $b = GeneralUtility::trimExplode('_', $a[0]);
                $uid = end($b);
                $pids[] = $uid;
            }

            return implode(',', $pids);
        }
        // TYPO3 8.7
        foreach ($pages as $page) {
            $pids[] = $page['uid'];
        }

        return implode(',', $pids);
    }
}
