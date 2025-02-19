<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(function () {

    // mk@rms, 2022-05-10
    // the width of images inside RTE will be reset to the default of 300 as soon as the content element is saved
    //  - /ext/rte_ckeditor_image/Classes/Database/RteImagesDbHook.php -> $magicImageService->setMagicImageMaximumDimensions($tsConfig['RTE.']['default.'])
    //  - public/typo3/sysext/core/Classes/Resource/Service/MagicImageService.php)
    // here the rteConfiguration array is always empty on save.
    // if we add the following line, the tsconfig will be loaded if this happens and the manual set RTE.default.buttons.image.options.magic.maxWidth = 1000 will be used
    ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE: EXT:ib_template/Configuration/TSconfig/ts_config.typoscript">');

    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['ibCustom'] = 'EXT:ib_template/Configuration/RTE/Default.yaml';
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['default'] = 'EXT:ib_template/Configuration/RTE/Default.yaml';
    ExtensionManagementUtility::addUserTSConfig('setup.override.edit_docModuleUpload = 0');
})();
