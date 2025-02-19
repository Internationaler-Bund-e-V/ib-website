<?php
namespace Ib\IbFormbuilder\Tests\Unit\Controller;

/**
 * Test case.
 *
 * @author Michael Kettel <mkettel@gmail.com>
 */
class FormControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Ib\IbFormbuilder\Controller\FormController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\Ib\IbFormbuilder\Controller\FormController::class)
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
    public function listActionFetchesAllFormsFromRepositoryAndAssignsThemToView()
    {

        $allForms = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formRepository = $this->getMockBuilder(\Ib\IbFormbuilder\Domain\Repository\FormRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $formRepository->expects(self::once())->method('findAll')->will(self::returnValue($allForms));
        $this->inject($this->subject, 'formRepository', $formRepository);

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('forms', $allForms);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenFormToView()
    {
        $form = new \Ib\IbFormbuilder\Domain\Model\Form();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('form', $form);

        $this->subject->showAction($form);
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenFormToFormRepository()
    {
        $form = new \Ib\IbFormbuilder\Domain\Model\Form();

        $formRepository = $this->getMockBuilder(\Ib\IbFormbuilder\Domain\Repository\FormRepository::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $formRepository->expects(self::once())->method('add')->with($form);
        $this->inject($this->subject, 'formRepository', $formRepository);

        $this->subject->createAction($form);
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenFormToView()
    {
        $form = new \Ib\IbFormbuilder\Domain\Model\Form();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('form', $form);

        $this->subject->editAction($form);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenFormInFormRepository()
    {
        $form = new \Ib\IbFormbuilder\Domain\Model\Form();

        $formRepository = $this->getMockBuilder(\Ib\IbFormbuilder\Domain\Repository\FormRepository::class)
            ->setMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $formRepository->expects(self::once())->method('update')->with($form);
        $this->inject($this->subject, 'formRepository', $formRepository);

        $this->subject->updateAction($form);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenFormFromFormRepository()
    {
        $form = new \Ib\IbFormbuilder\Domain\Model\Form();

        $formRepository = $this->getMockBuilder(\Ib\IbFormbuilder\Domain\Repository\FormRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $formRepository->expects(self::once())->method('remove')->with($form);
        $this->inject($this->subject, 'formRepository', $formRepository);

        $this->subject->deleteAction($form);
    }
}
