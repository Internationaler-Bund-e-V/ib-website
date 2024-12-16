<?php

declare(strict_types=1);

namespace IB\IbCmt\Controller;

use IB\IbCmt\Domain\Model\Content;
use IB\IbCmt\Domain\Repository\ContentRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/***
 *
 * This file is part of the "IB CMT" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020
 *
 ***/

/**
 * ContentController
 */
class ContentController extends ActionController
{
    /** @var array */
    private array $extensionConfiguration = [];

    /**
     * @var ContentRepository
     */
    protected $contentRepository = null;

    public function injectContentRepository(ContentRepository $contentRepository): void
    {
        $this->contentRepository = $contentRepository;
    }

    protected function initializeAction(): void
    {
        /** @var ExtensionConfiguration $config */
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->extensionConfiguration = $config->get('ib_cmt');

        //$this->contentRepository = $this->objectManager->get('IB\\IbCmt\\Domain\\Repository\\ContentRepository');
    }

    /**
     * action list
     */
    protected function listAction(): ResponseInterface
    {
        $content = $this->contentRepository->findByContenttype(0);
        $this->view->assign('contentItems', $content);
        $this->view->assignMultiple(array(
            'contentItems' => $content,
            'searchTerms' => $this->extensionConfiguration['termsTypo3'],
        ));

        return $this->htmlResponse();
    }

    /**
     * action list
     */
    public function listRedaktionAction(): ResponseInterface
    {
        $content = $this->contentRepository->findByContenttype(1);
        $this->view->assignMultiple(array(
            'contentItems' => $content,
            'searchTerms' => $this->extensionConfiguration['termsRT'],
        ));

        return $this->htmlResponse();
    }

    /**
     * action list
     */
    public function listNewsAction(): ResponseInterface
    {
        $content = $this->contentRepository->findByContenttype(2);
        $this->view->assignMultiple(array(
            'contentItems' => $content,
            'searchTerms' => $this->extensionConfiguration['termsTypo3'],
        ));

        return $this->htmlResponse();
    }

    /**
     * action allow
     * @IgnoreValidation("content")
     */
    public function allowAction(Content $content, string $redirect): never
    {
        if ($content->getAllowed()) {
            $content->setAllowed(false);
        } else {
            $content->setAllowed(true);
            $content->setTstampallowed($content->getContenttstamp());
        }
        $this->contentRepository->update($content);
        $this->redirect($redirect);
    }

    public function snippetsAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * action show
     */
    public function showAction(Content $content): ResponseInterface
    {
        $this->view->assign('content', $content);

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
    public function createAction(Content $newContent): never
    {
        $this->addFlashMessage(
            'The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html',
            '',
            AbstractMessage::WARNING
        );
        $this->contentRepository->add($newContent);
        $this->redirect('list');
    }

    /**
     * action edit
     * @IgnoreValidation("content")
     */
    public function editAction(Content $content): ResponseInterface
    {
        $this->view->assign('content', $content);

        return $this->htmlResponse();
    }

    /**
     * action update
     */
    public function updateAction(Content $content): never
    {
        $this->addFlashMessage(
            'The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html',
            '',
            AbstractMessage::WARNING
        );
        $this->contentRepository->update($content);
        $this->redirect('list');
    }

    /**
     * action delete
     */
    public function deleteAction(Content $content): never
    {
        $this->addFlashMessage(
            'The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html',
            '',
            AbstractMessage::WARNING
        );
        $this->contentRepository->remove($content);
        $this->redirect('list');
    }
}
