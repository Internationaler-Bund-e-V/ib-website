<?php

declare(strict_types=1);

use Ib\Ibjobs\Controller\JobsController;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

$_EXTKEY = 'ibjobs';
$extKey = 'ibjobs';

// ------------------------------------------------------------------------------------------------
// backend icons
// ------------------------------------------------------------------------------------------------
/** @var IconRegistry $iconRegistry */
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'tx-ibjobs-icon',
    BitmapIconProvider::class,
    ['source' => 'EXT:ibjobs/Resources/Public/Icons/wizard_rms_generic.png']
);

call_user_func(
    static function ($extKey) {
        ExtensionUtility::configurePlugin(
            'Ibjobs',
            'Iblogajobs',
            [
                JobsController::class => 'show',
            ],
            // non-cacheable actions
            [
                JobsController::class => 'show',
            ],
        );

        // wizards
        ExtensionManagementUtility::addPageTSConfig(
            'mod {
			wizards.newContentElement.wizardItems.ibjobs {
				header = IB Jobs
				elements {
					iblogajobs {
						icon = ' . ExtensionManagementUtility::extPath('ibjobs') . 'Resources/Public/Icons/wizard_rms_generic.png
						iconIdentifier = tx-ibjobs-icon
						title = LLL:EXT:ibjobs/Resources/Private/Language/locallang.xlf:title
						description = LLL:EXT:ibjobs/Resources/Private/Language/locallang.xlf:description
						tt_content_defValues {
							CType = list
							list_type = ibjobs_iblogajobs
						}
					}
				}
				show := addToList(iblogajobs)
			}
	   }'
        );
    },
    'ibjobs',
);
