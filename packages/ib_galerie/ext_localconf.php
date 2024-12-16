<?php

declare(strict_types=1);

use Rms\IbGalerie\Form\CodeRenderer;
use Rms\IbGalerie\Hooks\TCEmainHook;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

defined('TYPO3') || die('Access denied.');

$_EXTKEY = 'ib_galerie';

// Caching framework
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ib_galerie'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ib_galerie'] = array();
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ib_galerie']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['ib_galerie']['frontend'] = VariableFrontend::class;
}

//register rendering hook for gallery replacement
#$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
#    = \Rms\IbGalerie\Hooks\GalleryReplacerHook::class . '->contentPostProcOutput'; //= 'EXT:ib_galerie/Classes/Hooks/GalleryReplacerHook.php:GalleryReplacerHook->contentPostProcOutput';
/*
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
= 'EXT:ib_galerie/Classes/Hooks/GalleryReplacerHook.php:GalleryReplacerHook->cacheCall';
*/

//register hoook for updating gallery with code
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['ib_galerie'] = TCEmainHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['ib_galerie'] = TCEmainHook::class;

// Register a node in ext_localconf.php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1626702320] = [
    'nodeName' => 'codeRenderer',
    'priority' => 40,
    'class' => CodeRenderer::class,
];
