<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:mask/Resources/Private/Language/locallang_mask.xlf'][] = 'EXT:bv_bbe/Resources/Private/Language/custom.xlf';

// Register custom EXT:form configuration
ExtensionManagementUtility::addTypoScriptSetup(
    trim('
              module.tx_form {
                  settings {
                      yamlConfigurations {
                        100 = EXT:bv_bbe/Configuration/Form/CustomFormSetup.yaml
                      }
                  }
              }
          ')
);

/**
 * set ck_editor config file
 */
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['bv_bbe'] = 'EXT:bv_bbe/Configuration/RTE/bv_bbe.yaml';
