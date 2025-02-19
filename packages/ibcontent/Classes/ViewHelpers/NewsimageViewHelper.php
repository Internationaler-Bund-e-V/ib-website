<?php

declare(strict_types=1);

namespace Ib\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class NewsimageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('images', 'string', '', true);
    }

    public function render(): string
    {
        $images = $this->arguments['images'];

        return $this->extractFilename($images);
    }

    private function extractFilename(string $images): string
    {
        $imageArray = explode(',', $images);
        if ($imageArray !== []) {
            return $imageArray[0];
        }

        return $images;
    }
}
