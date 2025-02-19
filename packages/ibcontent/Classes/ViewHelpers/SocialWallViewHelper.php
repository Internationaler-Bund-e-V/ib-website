<?php

declare(strict_types=1);

namespace Ib\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SocialWallViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('code', 'string', 'flocklr social wall code', true);
        $this->registerArgument('type', 'string', 'flocklr social wall type', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $code = $arguments['code'];
        $type = $arguments['type'];

        $scriptSRC = '';
        $divID = '';
        $dom = new \DOMDocument();
        $dom->loadHTML($code);

        $scripts = $dom->getElementsByTagName('script');
        $divs = $dom->getElementsByTagName('div');
        //get script src
        if ($scripts->length > 0) {
            $scriptSRC = $scripts->item(0)->getAttribute('src');
        }
        //get div id
        if ($divs->length > 0) {
            $divID = $divs->item(0)->getAttribute('id');
        }

        if ($type == 'divID') {
            return $divID;
        }
        if ($type == 'script') {
            return $scriptSRC;
        } else {
            return null;
        }
    }
}
