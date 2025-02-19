<?php

declare(strict_types=1);

namespace Ib\Ibcontent\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ObfuscateEmailViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('emailAddress', 'string', 'The email address to encrypt', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $mailToEncrypt = $arguments['emailAddress'];

        $encryptedMail = '';
        $shift = rand(1, 10);
        for ($i = 0; $i < strlen((string)$mailToEncrypt); ++$i) {
            $n = ord($mailToEncrypt[$i]);
            $code = $n + $shift;
            if ($code > 127) {
                $code = $code - 127;
            }
            $encryptedMail .= chr($code);
        }

        return $encryptedMail . '#i3B1*' . $shift;
    }
}
