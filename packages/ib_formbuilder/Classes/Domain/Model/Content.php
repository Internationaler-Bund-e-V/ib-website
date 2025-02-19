<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * News
 */
class Content extends AbstractEntity
{
    protected string $pi_flexform = '';

    public function getPiFlexform(): string
    {
        return $this->pi_flexform;
    }
}
