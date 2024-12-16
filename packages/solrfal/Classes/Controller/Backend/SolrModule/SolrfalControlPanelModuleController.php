<?php

declare(strict_types=1);

/**
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

namespace ApacheSolrForTypo3\Solrfal\Controller\Backend\SolrModule;

use ApacheSolrForTypo3\Solr\Controller\Backend\Search\AbstractModuleController;
use ApacheSolrForTypo3\Solrfal\Queue\ItemRepository;
use Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Solrfal control panel
 *
 * @author Timo Hund <timo.hund@dkd.de>
 * @noinspection PhpUnused
 */
class SolrfalControlPanelModuleController extends AbstractModuleController
{

    /**
     * Index action
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function indexAction(): ResponseInterface
    {
        if ($this->selectedSite === null) {
            $this->view->assign('can_not_proceed', true);
            return $this->getModuleTemplateResponse();
        }

        $repository = $this->getItemRepository();
        $statistics = $repository->getStatisticsBySite($this->selectedSite);

        $this->view->assign('site_item_count', $statistics->getTotalCount());
        $this->view->assign('total_item_count', $repository->count());
        $this->view->assign('indexqueue_statistics', $statistics);
        $this->view->assign('indexqueue_errors', $repository->findErrorsBySite($this->selectedSite));
        return $this->getModuleTemplateResponse();
    }

    /**
     * Clears the file index queue for the current site.
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function clearSitesFileIndexQueueAction()
    {
        try {
            $this->getItemRepository()->removeBySite($this->selectedSite);
            $this->addFlashMessage(LocalizationUtility::translate('solrfal.backend.file_indexing_module.success.queue_emptied', 'Solrfal', [$this->selectedSite->getLabel()]));
        } catch (Exception $e) {
            $this->addFlashMessage(LocalizationUtility::translate('solrfal.backend.file_indexing_module.error.on_empty_queue', 'Solrfal', [$e->__toString()]), '', FlashMessage::ERROR);
        }

        $this->redirect('index');
    }

    /**
     * Removes all errors in the index queue list. So that the items can be indexed again.
     *
     * @throws StopActionException
     * @noinspection PhpUnused
     */
    public function resetLogErrorsAction()
    {
        $resetResult = $this->getItemRepository()->flushErrorsBySite($this->selectedSite);

        $label = 'solrfal.backend.file_indexing_module.flashmessage.success.reset_errors';
        $severity = FlashMessage::OK;
        if (!$resetResult) {
            $label = 'solrfal.backend.file_indexing_module.flashmessage.error.reset_errors';
            $severity = FlashMessage::ERROR;
        }

        $this->addFlashMessage(
            LocalizationUtility::translate($label, 'Solrfal'),
            LocalizationUtility::translate('solrfal.backend.file_indexing_module.flashmessage.title', 'Solrfal'),
            $severity
        );

        $this->redirect('index');
    }

    /**
     * Shows the error message for one queue item.
     *
     * @param int $indexQueueItemId
     * @noinspection PhpUnused
     */
    public function showErrorAction(int $indexQueueItemId)
    {
        if (is_null($indexQueueItemId)) {
            $severity = FlashMessage::ERROR;
            $this->addFlashMessage(
                LocalizationUtility::translate('solrfal.backend.file_indexing_module.flashmessage.error.no_queue_item_for_queue_error', 'Solrfal'),
                LocalizationUtility::translate('solrfal.backend.file_indexing_module.flashmessage.title', 'Solrfal'),
                $severity
            );

            return $this->getModuleTemplateResponse();
        }

        $item = $this->getItemRepository()->findByUid($indexQueueItemId);
        $this->view->assign('indexQueueItem', $item);
        return $this->getModuleTemplateResponse();
    }

    /**
     * @return ItemRepository
     */
    protected function getItemRepository(): ItemRepository
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(ItemRepository::class);
    }
}
