<?php

declare(strict_types=1);

namespace Rms\Ibcontent\ViewHelpers;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to render an image from a t3://file?uid=12345 reference.
 *
 * Usage: <rms:t3FilePath src="t3://file?uid=97027" />
 * Usage: {rms:t3FilePath(src: slide.ItemWrap.slideImage)}
 */
class T3FilePathViewHelper extends AbstractViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('src', 'string', 'T3 file reference URL', true);
    }

    /**
     * Render the ViewHelper
     *
     * @return string Rendered HTML tag
     */
    public function render(): string
    {
        $src = $this->arguments['src'] ?? '';
        $src = trim($src);

        if (empty($src) || $src == '') {
            return '';
        }

        // Extract UID from the src string
        $matches = [];
        preg_match('/t3:\/\/file\?uid=(\d+)/', $src, $matches);
        $uid = $matches[1] ?? null;

        if (!$uid) {
            return '';
        }

        try {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $fileObject = $resourceFactory->getFileObject((int)$uid);

            if ($fileObject) {
                $puburl = (string)$fileObject->getPublicUrl();
                $url_decoded = urldecode($puburl);
                #debug('xx' . $url_decoded);

                if ($url_decoded) {
                    return $url_decoded;
                }

                return '';
            } else {
                // Debugging information
                //GeneralUtility::devLog('File object is null for UID: ' . $uid, 'ib_content', 2);
            }
        } catch (\Exception $exception) {
            // Debugging information
            //GeneralUtility::devLog('Exception: ' . $e->getMessage(), 'ib_content', 3);

            return '';
        }

        return '';
    }
}
