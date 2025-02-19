<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Hooks;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NewsFlexFormHook
{
    /**
     * @param array $dataStructure
     * @param array $identifier
     * @return array
     */
    public function parseDataStructureByIdentifierPostProcess(array $dataStructure, array $identifier): array
    {
        if ($identifier['type'] === 'tca' && $identifier['tableName'] === 'tt_content' && $identifier['dataStructureKey'] === '*,news_pi1') {
            $file = Environment::getPublicPath() . 'EXT:ibcontent/Configuration/FlexForms/NewsCustomHeadline.xml';
            debug($file);
            $content = file_get_contents($file);
            if ($content) {
                $dataStructure['sheets']['extraEntry'] = GeneralUtility::xml2array($content);
            }
        }

        return $dataStructure;
    }
}
