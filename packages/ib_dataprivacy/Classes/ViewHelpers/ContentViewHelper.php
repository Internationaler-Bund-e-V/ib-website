<?php

declare(strict_types=1);

namespace Ib\IbDataprivacy\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ContentViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'int', 'UID of the content element', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $conf = array( // config
            'tables' => 'tt_content',
            'source' => $arguments['uid'],
            'dontCheckPid' => 1,
        );

        $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        //$rcObject = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\RecordsContentObject', $cObject);
        /** @var RecordsContentObject $rcObject */
        $rcObject = GeneralUtility::makeInstance(RecordsContentObject::class, $cObject);

        return $rcObject->render($conf);
    }
}
