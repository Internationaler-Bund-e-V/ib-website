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

/**
 * Class ItemGroup
 *
 * An ItemGroup is a set of documents that logically belongs together. It is used to merge documents into one
 * document that belong together.
 */
class ItemGroup
{
    /**
     * @var string
     */
    protected string $groupId = '';

    /**
     * @var Item[]
     */
    protected array $items = [];

    /**
     * @param Item $item
     */
    public function add(Item $item)
    {
        $this->items[$item->getUid()] = $item;
    }

    /**
     * @param Item $item
     */
    public function remove(Item $item)
    {
        unset($this->items[$item->getUid()]);
    }

    /**
     * Returns the smallest item uid.
     *
     * @return int
     */
    public function getRootItemUid(): int
    {
        return min(array_keys($this->items));
    }

    /**
     * Returns the item with the smallest uid.
     *
     * @return Item|null
     */
    public function getRootItem(): ?Item
    {
        $minUid = $this->getRootItemUid();
        return $this->items[$minUid] ?? null;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function getIsRootItem(Item $item): bool
    {
        return $this->getRootItemUid() === $item->getUid();
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function getHasOnlyRootItem(): bool
    {
        return count($this->items) === 1;
    }

    /**
     * @return bool
     */
    public function getIsEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     */
    public function setGroupId(string $groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
