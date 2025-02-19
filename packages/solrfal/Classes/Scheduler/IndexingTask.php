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

namespace ApacheSolrForTypo3\Solrfal\Scheduler;

use ApacheSolrForTypo3\Solr\System\Environment\CliEnvironment;
use ApacheSolrForTypo3\Solr\System\Environment\WebRootAllReadyDefinedException;
use ApacheSolrForTypo3\Solrfal\Indexing\Indexer;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Http\Client\ClientExceptionInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\ProgressProviderInterface;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class IndexingTask
 */
class IndexingTask extends AbstractTask implements ProgressProviderInterface
{
    protected int $fileCountLimit = 10;

    protected string $forcedWebRoot = '';

    /**
     * This is the main method that is called when a task is executed
     * It MUST be implemented by all classes inheriting from this one
     * Note that there is no error handling, errors and failures are expected
     * to be handled and logged by the client implementations.
     * Should return TRUE on successful execution, FALSE on error.
     *
     * @return bool Returns TRUE on successful execution, FALSE on error
     *
     * @throws WebRootAllReadyDefinedException
     * @throws FileDoesNotExistException
     * @throws ClientExceptionInterface
     */
    public function execute(): bool
    {
        $cliEnvironment = null;

        // Wrapped the CliEnvironment to avoid defining TYPO3_PATH_WEB since this
        // should only be done in the case when running it from outside TYPO3 BE
        // @see #921 and #934 on https://github.com/TYPO3-Solr
        if (Environment::isCli()) {
            /** @var CliEnvironment $cliEnvironment */
            $cliEnvironment = GeneralUtility::makeInstance(CliEnvironment::class);
            $cliEnvironment->backup();
            $cliEnvironment->initialize($this->getWebRoot());
        }

        $this->getIndexer()->processIndexQueue($this->fileCountLimit, false);

        if (Environment::isCli()) {
            $cliEnvironment->restore();
        }

        return true;
    }

    /**
     * Gets the progress of a task.
     *
     * @return float Progress of the task as a two decimal float. f.e. 44.87
     *
     * @throws DBALException
     */
    public function getProgress(): float
    {
        $itemsIndexedPercentage = 0.0;

        $totalItemsCount = $this->getItemRepository()->count();
        $outstandingItemCount = $this->getItemRepository()->countIndexingOutstanding();
        $failureItemCount = $this->getItemRepository()->countFailures();

        if ($totalItemsCount > 0) {
            $itemsIndexedCount      = $totalItemsCount - $outstandingItemCount - $failureItemCount;
            $itemsIndexedPercentage = $itemsIndexedCount * 100 / $totalItemsCount;
            $itemsIndexedPercentage = round($itemsIndexedPercentage, 2);
        }

        return $itemsIndexedPercentage;
    }

    /**
     * Returns some additional information about indexing progress, shown in
     * the scheduler's task overview list.
     *
     * @return string Information to display
     *
     * @throws DBALException
     */
    public function getAdditionalInformation(): string
    {
        $message = sprintf(
            $this->getLanguageService()->sL('LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.additionalInformation'),
            $this->getItemRepository()->countFailures()
        );

        $message .=  ' / Using webroot: ' . htmlspecialchars($this->getWebRoot());

        return $message;
    }

    /**
     * In the cli context TYPO3 has chance to determine the webroot.
     * Since we need it for the TSFE related things we allow to set it
     * in the scheduler task and use the ###PATH_typo3### marker in the
     * setting to be able to define relative paths.
     */
    public function getWebRoot(): string
    {
        if ($this->forcedWebRoot !== '') {
            return $this->replaceWebRootMarkers($this->forcedWebRoot);
        }

        // when nothing is configured, we use the Environment::getPublicPath()
        // which should fit in the most cases
        return Environment::getPublicPath();
    }

    protected function replaceWebRootMarkers(string $webRoot): string
    {
        if (str_contains($webRoot, '###PATH_typo3###')) {
            $webRoot = str_replace('###PATH_typo3###', Environment::getPublicPath() . '/typo3/', $webRoot);
        }

        if (str_contains($webRoot, '###PATH_site###')) {
            $webRoot = str_replace('###PATH_site###', Environment::getPublicPath(), $webRoot);
        }

        return $webRoot;
    }

    public function setFileCountLimit(int $fileCountLimit): void
    {
        $this->fileCountLimit = $fileCountLimit;
    }

    public function getFileCountLimit(): int
    {
        return $this->fileCountLimit;
    }

    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }

    protected function getIndexer(): Indexer
    {
        return GeneralUtility::makeInstance(Indexer::class);
    }

    public function setForcedWebRoot(string $forcedWebRoot): void
    {
        $this->forcedWebRoot = $forcedWebRoot;
    }

    public function getForcedWebRoot(): string
    {
        return $this->forcedWebRoot;
    }
}
