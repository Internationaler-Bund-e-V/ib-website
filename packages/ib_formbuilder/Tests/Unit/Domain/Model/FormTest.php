<?php
namespace Ib\IbFormbuilder\Tests\Unit\Domain\Model;

/**
 * Test case.
 *
 * @author Michael Kettel <mkettel@gmail.com>
 */
class FormTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Ib\IbFormbuilder\Domain\Model\Form
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Ib\IbFormbuilder\Domain\Model\Form();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getNameReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getName()
        );

    }

    /**
     * @test
     */
    public function setNameForStringSetsName()
    {
        $this->subject->setName('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'name',
            $this->subject
        );

    }

    /**
     * @test
     */
    public function getFormdataJsonReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getFormdataJson()
        );

    }

    /**
     * @test
     */
    public function setFormdataJsonForStringSetsFormdataJson()
    {
        $this->subject->setFormdataJson('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'formdataJson',
            $this->subject
        );

    }

    /**
     * @test
     */
    public function getReceiverReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getReceiver()
        );

    }

    /**
     * @test
     */
    public function setReceiverForStringSetsReceiver()
    {
        $this->subject->setReceiver('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'receiver',
            $this->subject
        );

    }
}
