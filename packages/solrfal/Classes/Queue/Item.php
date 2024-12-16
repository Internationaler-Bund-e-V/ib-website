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
    const STATE_BLOCKED = -1;

    const STATE_PENDING = 0;

    const STATE_INDEXED = 1;

    /**
     * @var int
     */
    protected $uid;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var int
     */
    protected $lastUpdate;

    /**
     * @var int
     */
    protected $lastIndexed;

    /**
     * @var int
     */
    protected $fileUid;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var bool
     */
    protected $error = false;

    /**
     * @var string
     */
    protected $merge_id = '';

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @param int $fileUid
     * @param ContextInterface $context
     * @param int $uid
     * @param bool $error
     * @param int $lastUpdate
     * @param int $lastIndexed
     * @param string $mergeId
     * @param string $errorMessage
     */
    public function __construct(int $fileUid, ContextInterface $context, $uid = 0, $error = false, $lastUpdate = 0, $lastIndexed = 0, $mergeId = '', $errorMessage = '')
    {
        $this->uid = (int)$uid;
        $this->fileUid = $fileUid;
        $this->context = $context;
        $this->lastIndexed = (int)$lastIndexed;
        $this->lastUpdate = (int)$lastUpdate;
        $this->error = (boolean)$error;
        $this->merge_id = $mergeId;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function getError(): bool
    {
        return $this->error;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return File
     * @throws FileDoesNotExistException
     */
    public function getFile(): File
    {
        if (!($this->file instanceof File)) {
            $this->file = $this->getResourceFactory()->getFileObject((int)($this->fileUid));
        }
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLastIndexed(): int
    {
        return $this->lastIndexed;
    }

    /**
     * @return int
     */
    public function getLastUpdate(): int
    {
        return $this->lastUpdate;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @param bool $error
     */
    public function setError($error)
    {
        $this->error = (boolean)$error;
    }

    /**
     * @param int $lastIndexed
     */
    public function setLastIndexed($lastIndexed)
    {
        $this->lastIndexed = (int)$lastIndexed;
    }

    /**
     * @param int $lastUpdate
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = (int)$lastUpdate;
    }

    /**
     * @return string
     */
    public function getMergeId(): string
    {
        return $this->merge_id;
    }

    /**
     * @param string $merge_id
     */
    public function setMergeId($merge_id)
    {
        $this->merge_id = $merge_id;
    }

    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        if ($this->getError()) {
            return self::STATE_BLOCKED;
        }

        if ($this->getLastIndexed() > $this->getLastUpdate()) {
            return self::STATE_INDEXED;
        }

        return self::STATE_PENDING;
    }
}
