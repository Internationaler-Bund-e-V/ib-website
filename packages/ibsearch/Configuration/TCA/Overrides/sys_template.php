<?php

declare(strict_types=1);

//
// load static typoscript configuration (selectable in the template module)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'ibsearch',
    'Configuration/TypoScript/',
    'IB ibsearch setup'
);
