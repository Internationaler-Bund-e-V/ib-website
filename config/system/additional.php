<?php

/**
 * #ddev-generated: Automatically generated TYPO3 additional.php file.
 * ddev manages this file and may delete or overwrite the file unless this comment is removed.
 * It is recommended that you leave this file alone.
 */

if (getenv('IS_DDEV_PROJECT') == 'true') {
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
        $GLOBALS['TYPO3_CONF_VARS'],
        [
            // This GFX configuration allows processing by installed ImageMagick 6
            'GFX' => [
                'processor' => 'ImageMagick',
                'processor_path' => '/usr/bin/',
                'processor_path_lzw' => '/usr/bin/',
            ],
            'SYS' => [
                'trustedHostsPattern' => '.*.*',
                'devIPmask' => '*',
                'displayErrors' => 1,
            ],
            'EXTENSIONS' => [
                'ib_cmt' => [
                    'pathRTJSON' => 'https://ib-redaktionstool.ddev.site/JSON/cmtExport.json',
                ],
                'ibcontent' => [
                    'urlIBPdb' => 'https://ib-redaktionstool.ddev.site',
                    'urlIBPdbImages' => 'https://ib-redaktionstool.ddev.site',
                    'urlIBPdbInteface' => 'https://ib-redaktionstool.ddev.site/api',
                ],
                'ibsearch' => [
                    'baseUrlPath' => 'https://ib-redaktionstool.ddev.site/upload/',
                    'baseUrlSolrInterface' => 'https://ib-redaktionstool.ddev.site/solrsearches/',
                ],
            ],
        ]
    );
}
