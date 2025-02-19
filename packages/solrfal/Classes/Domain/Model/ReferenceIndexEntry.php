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

namespace ApacheSolrForTypo3\Solrfal\Domain\Model;

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * A reference index entry
 */
class ReferenceIndexEntry
{
    /**
     * @var array<string, string|int|bool|null> $properties
     */
    protected array $properties;

    protected string $tableName;

    protected string $tableField;

    protected int $recordUid;

    protected string $referenceTableName;

    protected int $referenceUid;

    /**
     * @param array{
     *   hash: string,
     *   tablename: string,
     *   recuid: int,
     *   field: string,
     *   flexpointer: string,
     *   softref_key: string,
     *   softref_id: string,
     *   sorting: int,
     *   workspace: int,
     *   ref_table: string,
     *   ref_uid: int,
     *   ref_string: string,
     * } $referenceIndexRow
     */
    public function __construct(array $referenceIndexRow)
    {
        $this->properties = $referenceIndexRow;
        $this->tableName = $referenceIndexRow['tablename'];
        $this->tableField = $referenceIndexRow['field'];
        $this->recordUid = (int)$referenceIndexRow['recuid'];
        $this->referenceTableName = $referenceIndexRow['ref_table'];
        $this->referenceUid = (int)$referenceIndexRow['ref_uid'];
    }

    /**
     * Returns the table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Returns the table field
     */
    public function getTableField(): string
    {
        return $this->tableField;
    }

    /**
     * Returns the record uid
     */
    public function getRecordUid(): int
    {
        return $this->recordUid;
    }

    /**
     * Returns the record
     *
     * @return ?array<string, string|int|bool|null>
     */
    public function getRecord(): ?array
    {
        return BackendUtility::getRecord($this->getTableName(), $this->getRecordUid());
    }

    /**
     * Returns the reference table name
     */
    public function getReferenceTableName(): string
    {
        return $this->referenceTableName;
    }

    /**
     * Returns the reference uid
     */
    public function getReferenceUid(): int
    {
        return $this->referenceUid;
    }

    /**
     * Returns a record hash identifying the referenced record
     */
    public function getRecordHash(): string
    {
        return md5($this->getTableName() . $this->getRecordUid());
    }
}
