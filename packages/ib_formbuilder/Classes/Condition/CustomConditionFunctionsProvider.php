<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Condition;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CustomConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            $this->getMyPidInRootlineFunction(),
        ];
    }

    /**
     * see https://docs.typo3.org/m/typo3/reference-coreapi/10.4/en-us/ApiOverview/SymfonyExpressionLanguage/Index.html#sel-within-typoscript-conditions
     *
     * @return ExpressionFunction
     */
    protected function getMyPidInRootlineFunction(): ExpressionFunction
    {
        return new ExpressionFunction('mypidinrootline', static function () {
            // Not implemented, we only use the evaluator
        }, static function ($existingVariables, $pageIdToCheck) {

            $pageId = (int)GeneralUtility::_GP('id');
            $rootLine = BackendUtility::BEgetRootLine($pageId, '', true);

            foreach ($rootLine as $root) {
                if ((int)$root['uid'] === (int)$pageIdToCheck) {
                    return true;
                }
            }
        });
    }
}
