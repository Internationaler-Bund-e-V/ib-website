<?php

declare(strict_types=1);

namespace Ib\Ibcontent\EventListener;

use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class AfterFlexFormDataStructureParsedEventListener
{
    public function __invoke(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $dataStructure = $event->getDataStructure();
        $identifier = $event->getIdentifier();

        if ($identifier['type'] === 'tca' && $identifier['tableName'] === 'tt_content' && $identifier['dataStructureKey'] === '*,news_pi1') {
            $filePath = $this->getFilePath();

            $content = file_get_contents($this->getFilePath());

            if ($content) {
                //$dataStructure['sheets']['extraEntry'] = GeneralUtility::xml2array($content);
                ArrayUtility::mergeRecursiveWithOverrule($dataStructure['sheets'], GeneralUtility::xml2array($content));
            }
        }

        // Setze die geänderte Datenstruktur zurück in das Event
        $event->setDataStructure($dataStructure);
    }

    private function getFilePath(): string
    {
        $FLEXFORM = 'EXT:ibcontent/Configuration/FlexForms/NewsCustomHeadline.xml';

        return PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($FLEXFORM));
    }
}
