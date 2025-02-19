<?php

declare(strict_types=1);

namespace Rms\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ToolsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('filepath', 'string', 'path to file/document', true);
    }

    /**
     * @return string extracted filename
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $file_path = (string)$arguments['filepath'];
        if (empty($file_path)) {
            return '';
        }

        return self::extractFilename($file_path);
    }

    private static function extractFilename(string $filepath): string
    {
        return basename((string)$filepath);
    }
}
