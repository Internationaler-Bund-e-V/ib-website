<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Utility;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

class PagePropertiesHook extends AbstractPlugin
{
    /**
     * No cache
     */
    public function contentPostProcOutput(array &$params, TypoScriptFrontendController &$pObj): void
    {
        //  $pObj->content = preg_replace('#<title>.*<\/title>#','',$pObj->content);
    }
}
