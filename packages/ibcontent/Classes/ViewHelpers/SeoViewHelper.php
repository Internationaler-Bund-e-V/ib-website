<?php

declare(strict_types=1);

namespace Rms\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SeoViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('tag', 'string', '', true);
    }

    public function render(): void
    {
        $tag = $this->arguments['tag'];

        if ($tag == 'title') {
            $GLOBALS['TSFE']->page['title'] = trim((string)$this->renderChildren());
        } elseif ($tag == 'description') {
            $GLOBALS['TSFE']->page['description'] = trim((string)$this->renderChildren());
        }
    }
}
