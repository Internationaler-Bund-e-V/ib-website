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

namespace ApacheSolrForTypo3\Solrfal\Scheduler;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional field provider for the IndexingTask
 *
 * @author Markus Goldbach <markus.goldbach@dkd.de>
 * @author Ingo Renner <ingo@typo3.org>
 * @author Steffen Ritter <steffen.ritter@typo3.org>
 */
class IndexingTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * @param array $taskInfo
     * @param IndexingTask $task
     * @param SchedulerModuleController $schedulerModule
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];

        // set default value
        if ($schedulerModule->getCurrentAction() == 'add') {
            $taskInfo['filesToIndexLimit'] = 10;
            $taskInfo['forcedWebRoot'] = '';
        }

        if ($schedulerModule->getCurrentAction() == 'edit') {
            $taskInfo['filesToIndexLimit'] = $task->getFileCountLimit();
            $taskInfo['forcedWebRoot'] = $task->getForcedWebRoot();
        }

        $additionalFields['filesToIndexLimit'] = [
            'code'     => '<input type="text" name="tx_scheduler[filesToIndexLimit]" value="' . (int)($taskInfo['filesToIndexLimit']) . '" />',
            'label'    => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.fields.filesToIndexLimit',
            'cshKey'   => '',
            'cshLabel' => '',
        ];

        $additionalFields['forcedWebRoot'] = [
            'code' => '<input type="text" name="tx_scheduler[forcedWebRoot]" value="' . htmlspecialchars($taskInfo['forcedWebRoot']) . '" />',
            'label' => 'LLL:EXT:solrfal/Resources/Private/Language/locallang.xlf:scheduler.fields.forcedWebRoot',
            'cshKey' => '',
            'cshLabel' => '',
        ];

        return $additionalFields;
    }
    /**
     * Checks any additional data that is relevant to this task. If the task
     * class is not relevant, the method is expected to return true
     *
     * @param array	$submittedData reference to the array containing the data submitted by the user
     * @param SchedulerModuleController	$schedulerModule reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     * @noinspection PhpUnused
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        if (!MathUtility::canBeInterpretedAsInteger($submittedData['filesToIndexLimit'])) {
            return false;
        }
        // check limit
        $submittedData['filesToIndexLimit'] = (int)($submittedData['filesToIndexLimit']);
        return true;
    }
    /**
     * Saves any additional input into the current task object if the task
     * class matches.
     *
     * @param array $submittedData array containing the data submitted by the user
     * @param AbstractTask $task reference to the current task object
     * @noinspection PhpUnused
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->setFileCountLimit($submittedData['filesToIndexLimit']);
        $task->setForcedWebRoot($submittedData['forcedWebRoot']);
    }
}
