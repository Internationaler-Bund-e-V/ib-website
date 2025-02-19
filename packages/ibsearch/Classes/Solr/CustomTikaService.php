<?php

declare(strict_types=1);

namespace Ib\Ibsearch\Solr;

use ApacheSolrForTypo3\Tika\Service\Tika\AppService;
use ApacheSolrForTypo3\Tika\Utility\FileUtility;
use ApacheSolrForTypo3\Tika\Utility\ShellUtility;
use TYPO3\CMS\Core\Utility\CommandUtility;

class CustomTikaService extends AppService
{
    public function customExtractText(string $filePath): string|false|null
    {
        $tikaCommand = ShellUtility::getLanguagePrefix()
            . CommandUtility::getCommand('java')
            . ' -Dapple.awt.UIElement=true'
            . ' -Dfile.encoding=UTF8' // forces UTF8 output
            . ' -jar ' . escapeshellarg((string)FileUtility::getAbsoluteFilePath($this->configuration['tikaPath']))
            . ' -J'
            . ' ' . ShellUtility::escapeShellArgument($filePath);
        //. ' 2> /dev/null';

        $extractedText = shell_exec($tikaCommand);

        return $extractedText;
    }
}
