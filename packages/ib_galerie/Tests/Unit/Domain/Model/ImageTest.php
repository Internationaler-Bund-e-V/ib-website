<?php
namespace Rms\IbGalerie\Tests\Unit\Domain\Model;

/**
 * Test case.
 */
class ImageTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Rms\IbGalerie\Domain\Model\Image
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Rms\IbGalerie\Domain\Model\Image();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function dummyTestToNotLeaveThisFileEmpty()
    {
        self::markTestIncomplete();
    }
}
