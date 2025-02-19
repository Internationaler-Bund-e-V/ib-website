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

namespace ApacheSolrForTypo3\Solrfal\Queue;

use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Item
 */
class Item
{
    public const STATE_BLOCKED = -1;

    public const STATE_PENDING = 0;

    public const STATE_INDEXED = 1;

    protected ?File $file = null;

    public function __construct(
        protected readonly int $fileUid,
        protected readonly ContextInterface $context,
        protected readonly int $uid = 0,
        protected bool $error = false,
        protected int $lastUpdated = 0,
        protected int $lastIndexed = 0,
        protected string $mergeId = '',
        protected readonly string $errorMessage = '',
    ) {}

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getError(): bool
    {
        return $this->error;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @throws FileDoesNotExistException
     */
    public function getFile(): File
    {
        if (!($this->file instanceof File)) {
            $this->file = $this->getResourceFactory()->getFileObject($this->fileUid);
        }
        return $this->file;
    }

    public function getLastIndexed(): int
    {
        return $this->lastIndexed;
    }

    public function getLastUpdated(): int
    {
        return $this->lastUpdated;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setError(bool $error): void
    {
        $this->error = $error;
    }

    public function setLastIndexed(int $lastIndexed): void
    {
        $this->lastIndexed = $lastIndexed;
    }

    public function setLastUpdated(int $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getMergeId(): string
    {
        return $this->mergeId;
    }

    public function setMergeId(string $mergeId): void
    {
        $this->mergeId = $mergeId;
    }

    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }

    public function getState(): int
    {
        if ($this->getError()) {
            return self::STATE_BLOCKED;
        }

        if ($this->getLastIndexed() > $this->getLastUpdated()) {
            return self::STATE_INDEXED;
        }

        return self::STATE_PENDING;
    }
}
