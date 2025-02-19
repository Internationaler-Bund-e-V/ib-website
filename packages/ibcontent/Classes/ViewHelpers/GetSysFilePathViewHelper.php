<?php

declare(strict_types=1);

namespace Ib\Ibcontent\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * get the path of a given sys_file record identified by its uid
 *
 * @author mkettel, 2021-08-03
 *
 * usage import {namespace ib=Ib\Ibcontent\ViewHelpers}
 * uage inline use {ib:getSysFilePath(uid: 6)}
 *
 * Class HtmlNotEmptyViewHelper
 * @package Ib\Ibcontent\ViewHelpers
 */
class GetSysFilePathViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'int', 'The uid of the record.', true);
    }

    public function render(): string
    {
        $result = BackendUtility::getRecord('sys_file', $this->arguments['uid']);

        $path_prefix = "";
        if (strpos((string)$result['identifier'], 'fileadmin') === false) {
            $path_prefix .= "/fileadmin";
        }
        if (strpos((string)$result['identifier'], 'user_upload') === false) {
            $path_prefix .= "/user_upload";
        }
        if (strpos((string)$result['identifier'], 'storage_ib_redaktion') === false) {
            $path_prefix .= "/storage_ib_redaktion";
        }

        $result['identifier'] = $path_prefix . $result['identifier'];

        //debug($result['identifier']);
        //return (empty($result) === false) ? $result['identifier'] : false;

        $output = $result['identifier'];

        return $output;
    }
}
