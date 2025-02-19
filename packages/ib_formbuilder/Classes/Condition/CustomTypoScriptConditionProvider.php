<?php

declare(strict_types=1);

namespace Ib\IbFormbuilder\Condition;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

class CustomTypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            CustomConditionFunctionsProvider::class,
        ];
    }
}
