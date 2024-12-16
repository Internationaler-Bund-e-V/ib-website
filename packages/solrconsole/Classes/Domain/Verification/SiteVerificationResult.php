<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
namespace ApacheSolrForTypo3\Solrconsole\Domain\Verification;

class SiteVerificationResult
{
    protected $globalErrors = [];

    protected $configurationVerificationResults = [];

    /**
     * @param string $errorMessage
     */
    public function addGlobalError(string $errorMessage)
    {
        $this->globalErrors[] = $errorMessage;
    }

    /**
     * @return array
     */
    public function getGlobalErrors(): array
    {
        return $this->globalErrors;
    }

    /**
     * @param ConfigurationVerificationResult $configurationVerificationResult
     */
    public function addConfigurationVerificationResult(ConfigurationVerificationResult $configurationVerificationResult)
    {
        $this->configurationVerificationResults[] = $configurationVerificationResult;
    }

    /**
     * @return ConfigurationVerificationResult[]
     */
    public function getConfigurationVerificationResults(): array
    {
        return $this->configurationVerificationResults;
    }
}