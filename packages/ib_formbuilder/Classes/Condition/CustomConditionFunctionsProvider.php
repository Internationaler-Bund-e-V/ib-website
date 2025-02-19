<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Condition;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CustomConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            $this->getMyPidInRootlineFunction(),
        ];
    }

    /**
     * Custom condition to check if a given page ID is in the rootline
     */
    protected function getMyPidInRootlineFunction(): ExpressionFunction
    {
        return new ExpressionFunction('mypidinrootline', static function () {
            // Not implemented, we only use the evaluator
        }, static function ($existingVariables, $pageIdToCheck) {
            $pageId = self::getCurrentPageId();

            if (!$pageId) {
                return false;
            }

            // Fetch the rootline using BackendUtility::BEgetRootLine
            $rootLine = BackendUtility::BEgetRootLine($pageId);

            foreach ($rootLine as $root) {
                if ((int)$root['uid'] === (int)$pageIdToCheck) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Get the current page ID, working for both frontend and backend
     */
    private static function getCurrentPageId(): ?int
    {
        // Check if we're in the frontend
        if (isset($GLOBALS['TSFE']) && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            return (int)$GLOBALS['TSFE']->id;
        }

        // Otherwise, fallback to backend request
        return (int)GeneralUtility::_GP('id') ?: null;
    }
}
