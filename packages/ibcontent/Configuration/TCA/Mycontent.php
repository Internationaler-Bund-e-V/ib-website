<?php

declare(strict_types=1);

if (!defined('TYPO3')) {
    die('Access denied.');
}
/*
 * render custom content header
 */
$GLOBALS['TCA']['tt_content']['ctrl']['label_userFunc'] = 'Ib\\Ibcontent\\Utility\\ContentElementLabelRenderer->getContentElementTitle';
