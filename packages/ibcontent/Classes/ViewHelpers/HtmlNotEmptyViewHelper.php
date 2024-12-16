<?php

declare(strict_types=1);

namespace Rms\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * this helper checks a string if it is empty. the inital version just checks the strlength()
 * can be extended to check if content only contains an empty <div>. That is something the RTE sometime
 * saves if the editor is not mindful
 *
 * @author mkettel, 2018-02-26
 * @usage import {namespace rms=Rms\Ibcontent\ViewHelpers
 * @uage inline use in <f:if> condition <f:if condition="{rms:htmlNotEmpty(html:item.ItemWrap.linkButtonLabel)}">
 *
 * Class HtmlNotEmptyViewHelper
 * @package Rms\Ibcontent\ViewHelpers
 */
class HtmlNotEmptyViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('html', 'string', '', true);
    }

    public function render(): bool
    {
        $html = $this->arguments['html'];

        return $this->htmlNotEmpty($html);
    }

    private function htmlNotEmpty(string $html): bool
    {
        $toReturn = false;
        $html = trim($html);
        if (strlen($html) > 0) {
            $toReturn = true;
        }

        return $toReturn;
    }
}
