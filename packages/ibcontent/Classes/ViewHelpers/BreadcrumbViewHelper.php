<?php

declare(strict_types=1);

namespace Ib\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class BreadcrumbViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        //$this -> registerArgument('additionalPages');
        $this->registerArgument('additionalPages', 'array', 'additional pages for breadcrumb');
    }

    /**
     * @return mixed returns array with page path and properties
     */
    public function render()
    {

        $pagePathArray = array();
        $rootLine = array_reverse($GLOBALS['TSFE']->rootLine);

        $rlCount = count($rootLine);

        for ($i = 0; $i < $rlCount - 1; $i++) {
            $page = $rootLine[$i];
            if ($page['nav_hide'] != 1) {
                $tempPage = array();
                $tempPage['uid'] = $page['uid'];
                //$tempPage['title'] = $page['title'];
                $tempPage['title'] = $page['nav_title'];
                $tempPage['type'] = 'typo';
                $pagePathArray[] = $tempPage;
            }
        }

        $apCount = count($this->arguments['additionalPages']);
        for ($i = 0; $i < $apCount; $i++) {
            $page = $this->arguments['additionalPages'][$i];
            if (!isset($page['id'])) {
                $page['id'] = 0;
            }
            $tempPage = array();
            $tempPage['title'] = (strlen((string) $page['name']) > 20) ? substr((string) $page['name'], 0, 20) . "..." : $page['name'];
            $tempPage['id'] = $page['id'];
            $tempPage['type'] = 'db';
            $tempPage['renderLink'] = ($i < $apCount - 1 && $page['status']) ? true : false;
            $pagePathArray[] = $tempPage;
        }

        return $pagePathArray;
    }
}
