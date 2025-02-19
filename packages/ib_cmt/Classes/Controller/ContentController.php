<?php

declare(strict_types=1);

namespace Ib\IbCmt\Controller;

use Ib\IbCmt\Domain\Model\Content;
use Ib\IbCmt\Domain\Repository\ContentRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
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

    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(ContentRepository $contentRepository, ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->contentRepository = $contentRepository;

        /** @var ExtensionConfiguration $config */
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->extensionConfiguration = $config->get('ib_cmt');

        $this->contentRepository = GeneralUtility::makeInstance(ContentRepository::class);
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    /**
     * action list
     */
    protected function listAction(): ResponseInterface
    {
        $content = $this->contentRepository->findByContenttype(0);
        $this->view->assign('contentItems', $content);

        $values = array(
            'contentItems' => $content,
            'searchTerms' => $this->extensionConfiguration['termsTypo3'],
        );

        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibCmtModules')->assignMultiple($values)->renderResponse('List');
    }

    /**
     * action list
     */
    public function listRedaktionAction(): ResponseInterface
    {
        $content = $this->contentRepository->findByContenttype(1);

        $values = array(
            'contentItems' => $content,
            'searchTerms' => $this->extensionConfiguration['termsRT'],
        );

        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibCmtModules')->assignMultiple($values)->renderResponse('ListRedaktion');
    }

    /**
     * action list
     */
    public function listNewsAction(): ResponseInterface
    {
        $content = $this->contentRepository->findByContenttype(2);

        $values = array(
            'contentItems' => $content,
            'searchTerms' => $this->extensionConfiguration['termsTypo3'],
        );

        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibCmtModules')->assignMultiple($values)->renderResponse('ListNews');
    }

    /**
     * action allow
     */
    #[IgnoreValidation(['argumentName' => 'content'])]
    public function allowAction(Content $content, string $redirect): ResponseInterface
    {
        if ($content->getAllowed()) {
            $content->setAllowed(false);
        } else {
            $content->setAllowed(true);
            $content->setTstampallowed($content->getContenttstamp());
        }
        $this->contentRepository->update($content);

        return $this->redirect($redirect);
    }

    public function snippetsAction(): ResponseInterface
    {
        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibCmtModules')->renderResponse('Snippets');
    }
}
