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

namespace ApacheSolrForTypo3\Solrfal\System\Links;

/**
 * Class FileLinkExtractionService
 */
class FileLinkExtractionService
{
    /**
     * Extracts all fileLinks in the format t3://
     *
     * @return string[]
     */
    public function extract(string $content): array
    {
        $fileLinks = [];
        $regexResult = [];
        preg_match_all('/(?<file>t3:\/\/file?[^>" ]*)/i', $content, $regexResult);
        foreach ($regexResult['file'] as $fileLink) {
            $fileLinks[] = $fileLink;
        }

        return $fileLinks;
    }
}
