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

namespace ApacheSolrForTypo3\Solrfal\Tests\Unit\Queue;

use ApacheSolrForTypo3\Solrfal\Context\ContextInterface;
use ApacheSolrForTypo3\Solrfal\Queue\Item;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class Item
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class ItemTest extends UnitTest
{
    public function itemStates()
    {
        return [
            'pending' => [false, 12, 10, Item::STATE_PENDING],
            'blocked' => [true, 12, 10, Item::STATE_BLOCKED],
            'indexed' => [false, 12, 13, Item::STATE_INDEXED],
        ];
    }
    /**
     * @dataProvider itemStates
     * @test
     */
    public function canGetState(bool $hasError, int $lastUpdate, int $lastIndexed, int $expectedState)
    {
        $contextMock = $this->getDumbMock(ContextInterface::class);
        $item = new Item(4711, $contextMock, 888, $hasError, $lastUpdate, $lastIndexed);

        self::assertSame($expectedState, $item->getState());
    }
}
