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

namespace ApacheSolrForTypo3\Solrconsole\Command\OptionHelper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractCommaSeparatedList
{
    /**
     * @var string
     */
    protected string $option = '';

    /**
     * @var string
     */
    protected string $defaultValue = '';

    /**
     * @var string
     */
    protected string $label;

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return array
     */
    public function run(SymfonyStyle $io, InputInterface $input): array
    {
        $configurationOption = (string)$input->getOption($this->option);
        $configurationOption = trim($configurationOption) === '' ? $this->defaultValue : $configurationOption;
        $this->renderAffectedConfigurations($io, $configurationOption);
        return $this->getConfigurationsArrayFromString($configurationOption);
    }

    /**
     * Builds the expected configuration array from a passed list of indexing configuration names.
     *
     * @param $configuration
     * @return array
     */
    protected function getConfigurationsArrayFromString($configuration): array
    {
        return $configuration === '*' ? [] : GeneralUtility::trimExplode(',', $configuration);
    }

    /**
     * Renders the affected indexing configurations.
     *
     * @param SymfonyStyle $io
     * @param $configurationOption
     */
    protected function renderAffectedConfigurations(SymfonyStyle $io, $configurationOption)
    {
        $io->writeln($this->label . ':');

        $configurations = ($configurationOption === '*') ? ['all'] : GeneralUtility::trimExplode(',', $configurationOption);
        $io->writeln('   ' . implode(', ', $configurations));
        $io->newLine();
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }
}
