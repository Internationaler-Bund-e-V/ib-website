<?php
namespace Rms\IbGalerie\Tests\Unit\Controller;

/**
 * Test case.
 */
class GalerieControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Rms\IbGalerie\Controller\GalerieController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\Rms\IbGalerie\Controller\GalerieController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllGaleriesFromRepositoryAndAssignsThemToView()
    {

        $allGaleries = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $galerieRepository = $this->getMockBuilder(\Rms\IbGalerie\Domain\Repository\GalerieRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $galerieRepository->expects(self::once())->method('findAll')->will(self::returnValue($allGaleries));
        $this->inject($this->subject, 'galerieRepository', $galerieRepository);

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('galeries', $allGaleries);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenGalerieToView()
    {
        $galerie = new \Rms\IbGalerie\Domain\Model\Galerie();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('galerie', $galerie);

        $this->subject->showAction($galerie);
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenGalerieToGalerieRepository()
    {
        $galerie = new \Rms\IbGalerie\Domain\Model\Galerie();

        $galerieRepository = $this->getMockBuilder(\Rms\IbGalerie\Domain\Repository\GalerieRepository::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $galerieRepository->expects(self::once())->method('add')->with($galerie);
        $this->inject($this->subject, 'galerieRepository', $galerieRepository);

        $this->subject->createAction($galerie);
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenGalerieToView()
    {
        $galerie = new \Rms\IbGalerie\Domain\Model\Galerie();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('galerie', $galerie);

        $this->subject->editAction($galerie);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenGalerieInGalerieRepository()
    {
        $galerie = new \Rms\IbGalerie\Domain\Model\Galerie();

        $galerieRepository = $this->getMockBuilder(\Rms\IbGalerie\Domain\Repository\GalerieRepository::class)
            ->setMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $galerieRepository->expects(self::once())->method('update')->with($galerie);
        $this->inject($this->subject, 'galerieRepository', $galerieRepository);

        $this->subject->updateAction($galerie);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenGalerieFromGalerieRepository()
    {
        $galerie = new \Rms\IbGalerie\Domain\Model\Galerie();

        $galerieRepository = $this->getMockBuilder(\Rms\IbGalerie\Domain\Repository\GalerieRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $galerieRepository->expects(self::once())->method('remove')->with($galerie);
        $this->inject($this->subject, 'galerieRepository', $galerieRepository);

        $this->subject->deleteAction($galerie);
    }
}
