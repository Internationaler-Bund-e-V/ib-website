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
use Doctrine\DBAL\Exception as DBALException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Solrfal control panel
 *
 * @noinspection PhpUnused
 */
class SolrfalControlPanelModuleController extends AbstractModuleController
{
    /**
     * Index action
     *
     * @throws DBALException
     */
    public function indexAction(): ResponseInterface
    {
        if ($this->selectedSite === null) {
            $this->moduleTemplate->assign('can_not_proceed', true);
            return $this->moduleTemplate->renderResponse('Index');
        }

        $repository = $this->getItemRepository();
        $statistics = $repository->getStatisticsBySite($this->selectedSite);

        $this->moduleTemplate->assign('site_item_count', $statistics->getTotalCount());
        $this->moduleTemplate->assign('total_item_count', $repository->count());
        $this->moduleTemplate->assign('indexqueue_statistics', $statistics);
        $this->moduleTemplate->assign('indexqueue_errors', $repository->findErrorsBySite($this->selectedSite));
        return $this->moduleTemplate->renderResponse('Index');
    }

    /**
     * Clears the file index queue for the current site.
     *
     * @noinspection PhpUnused
     */
    public function clearSitesFileIndexQueueAction(): ResponseInterface
    {
        try {
            $this->getItemRepository()->removeBySite($this->selectedSite);
            $this->addFlashMessage(LocalizationUtility::translate('solrfal.backend.file_indexing_module.success.queue_emptied', 'Solrfal', [$this->selectedSite->getLabel()]));
        } catch (Throwable $e) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'solrfal.backend.file_indexing_module.error.on_empty_queue',
                    'Solrfal',
                    [$e->__toString()]
                ),
                '',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this->redirect('index');
    }

    /**
     * Removes all errors in the index queue list. So that the items can be indexed again.
     *
     * @noinspection PhpUnused
     */
    public function resetLogErrorsAction(): ResponseInterface
    {
        $resetResult = $this->getItemRepository()->flushErrorsBySite($this->selectedSite);

        $label = 'solrfal.backend.file_indexing_module.flashmessage.success.reset_errors';
        $severity = ContextualFeedbackSeverity::OK;
        if (!$resetResult) {
            $label = 'solrfal.backend.file_indexing_module.flashmessage.error.reset_errors';
            $severity = ContextualFeedbackSeverity::ERROR;
        }

        $this->addFlashMessage(
            LocalizationUtility::translate($label, 'Solrfal'),
            LocalizationUtility::translate('solrfal.backend.file_indexing_module.flashmessage.title', 'Solrfal'),
            $severity
        );

        return $this->redirect('index');
    }

    /**
     * Shows the error message for one queue item.
     *
     * @throws DBALException
     * @noinspection PhpUnused
     */
    public function showErrorAction(?int $indexQueueItemId): ResponseInterface
    {
        if (is_null($indexQueueItemId)) {
            $severity = ContextualFeedbackSeverity::ERROR;
            $this->addFlashMessage(
                LocalizationUtility::translate('solrfal.backend.file_indexing_module.flashmessage.error.no_queue_item_for_queue_error', 'Solrfal'),
                LocalizationUtility::translate('solrfal.backend.file_indexing_module.flashmessage.title', 'Solrfal'),
                $severity
            );

            return $this->moduleTemplate->renderResponse('ShowError');
        }

        $item = $this->getItemRepository()->findByUid($indexQueueItemId);
        $this->moduleTemplate->assign('indexQueueItem', $item);
        return $this->moduleTemplate->renderResponse('ShowError');
    }

    protected function getItemRepository(): ItemRepository
    {
        return GeneralUtility::makeInstance(ItemRepository::class);
    }
}
