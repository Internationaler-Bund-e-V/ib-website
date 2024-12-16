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
use ApacheSolrForTypo3\Solrfal\Queue\ItemGroup;
use ApacheSolrForTypo3\Solrfal\Tests\Unit\UnitTest;

/**
 * Class ItemMergeSetTest
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class ItemGroupTest extends UnitTest
{
    /**
     * @test
     */
    public function canGetRootItem()
    {
        $contextMock = $this->getDumbMock(ContextInterface::class);

        $itemGroup = new ItemGroup();
        $item1 = new Item(1, $contextMock, 14);
        $item2 = new Item(2, $contextMock, 12);
        $item3 = new Item(3, $contextMock, 22);

        $itemGroup->add($item1);
        $itemGroup->add($item2);
        $itemGroup->add($item3);

        self::assertSame($item2, $itemGroup->getRootItem(), 'ItemSet retrieved unexpected root item');
        self::assertTrue($itemGroup->getIsRootItem($item2), 'Could not detect root item');
    }
}
