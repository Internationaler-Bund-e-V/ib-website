<?php

declare(strict_types=1);

namespace Rms\IbGalerie\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;

class TCEmainHook
{
    public function processDatamap_postProcessFieldArray( // phpcs:ignore
        string $status,
        string $table,
        string $id,
        array &$fieldArray,
        DataHandler &$pObj
    ): void {
        if ($status == 'new' && $table == 'tx_ibgalerie_domain_model_galerie') {
            $fieldArray['code'] = '###IBG_' . str_replace("NEW", "", (string)$id) . '_###';
        }
    }
}
