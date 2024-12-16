<?php

declare(strict_types=1);

namespace Rms\IbGalerie\Controller;

use Psr\Http\Message\ResponseInterface;
use Rms\IbGalerie\Domain\Model\Galerie;
use Rms\IbGalerie\Domain\Repository\GalerieRepository;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/***
 *
 * This file is part of the "ibgalerie" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018
 *
 ***/

/**
 * GalerieController
 */
class GalerieController extends ActionController
{
    /**
     * galerieRepository
     *
     * @var GalerieRepository
     */
    protected GalerieRepository $galerieRepository;

    /**
     * @param GalerieRepository $galerieRepository
     */
    public function __construct(GalerieRepository $galerieRepository)
    {
        $this->galerieRepository = $galerieRepository;
    }

    /**
     * action list
     */
    public function listAction(): ResponseInterface
    {
        $galeries = $this->galerieRepository->findAll();
        $this->view->assign('galeries', $galeries);

        return $this->htmlResponse();
    }

    /**
     * action show
     */
    public function showAction(Galerie $galerie): ResponseInterface
    {
        $this->view->assign('galerie', $galerie);

        return $this->htmlResponse();
    }

    /**
     * action new
     */
    public function newAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * action create
     */
    public function createAction(Galerie $newGalerie): never
    {
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', AbstractMessage::WARNING);
        $this->galerieRepository->add($newGalerie);
        $this->redirect('list');
    }

    /**
     * action edit
     */
    public function editAction(Galerie $galerie): ResponseInterface
    {
        $this->view->assign('galerie', $galerie);

        return $this->htmlResponse();
    }

    /**
     * action update
     */
    public function updateAction(Galerie $galerie): never
    {
        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', AbstractMessage::WARNING);
        $this->galerieRepository->update($galerie);
        $this->redirect('list');
    }

    /**
     * action delete
     */
    public function deleteAction(Galerie $galerie): never
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', AbstractMessage::WARNING);
        $this->galerieRepository->remove($galerie);
        $this->redirect('list');
    }
}
