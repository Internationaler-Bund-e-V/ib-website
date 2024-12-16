<?php

declare(strict_types=1);

namespace Rms\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class JobtagViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('jobtags', 'string', 'path to file/document', true);
    }

    /**
     * @return string extracted jobtags
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return self::extractJobtags($arguments['jobtags']);
    }

    private static function extractJobtags(array $jobtags): string
    {
        $tmpJobtags = array();

        foreach ($jobtags as $jobtag) {
            $tmpJobtags[] = "-" . $jobtag['id'] . "-";
        }

        return implode(" ", $tmpJobtags);
    }
}
