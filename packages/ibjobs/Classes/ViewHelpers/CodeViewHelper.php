<?php

declare(strict_types=1);

namespace Ib\Ibjobs\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('func', 'string', 'name of function', true);
        $this->registerArgument('param', 'mixed', 'parameter of function', true);
    }

    public function render(): string
    {
        $func   = $this->arguments['func'];
        $param  = $this->arguments['param'];

        $toreturn = "";

        switch ($func) {
            case 'convertCode':
                $toreturn = $this->convertCode($param);
                break;
            case 'convertDate':
                $toreturn = $this->getDate($param);
                break;
            case 'getTextBlocks':
                $toreturn = $this->getTextBlocks($param);
                break;
        }

        return $toreturn;
    }

    private function convertCode(string $code): string
    {
        $code = str_replace(array('#cr#', '#az#'), array('<br>', ' &bull; '), (string)$code);

        return $code;
    }

    private function getDate(string $date): string
    {
        return date('d.m.Y', (int)strtotime((string)$date));
    }

    /**
     * Returns li block from "Textvorlagen" in jobs xml
     * 101-110 -> "Wir bieten Ihnen"
     * 201-210 -> "Sie bringen mit"
     * 301-310 -> "Sie erleben bei uns"
     * 401-410 ->
     */
    private function getTextBlocks(array $param): string
    {
        $counter    = 0;
        $block      = $param['posStart'];

        //map first linke of block to array key
        $oneLineValue = "";
        $param['ul'] = array_filter($param['ul']);

        //count rows in block
        foreach ($param['ul'] as $ul) {
            if (substr((string)$ul['tt_rang'], 0, 1) == $block && !empty($ul['tt_text'])) {
                if (substr((string)$ul['tt_rang'], 02, 3) == '1') {
                    $oneLineValue = $ul['tt_text'];
                }
                $counter++;
            }
        }

        if ($counter == 1) {
            $content = $oneLineValue;
        } else {
            $content = "<ul>";
            foreach ($param['ul'] as $ul) {
                if ((substr((string)$ul['tt_rang'], 0, 1) == $block) && (is_string($ul['tt_text']) && strlen($ul['tt_text']) > 0)) {
                    $content .= "<li>" . $ul['tt_text'] . "</li>";
                }
            }
            $content .= "</ul>";
        }

        return $content;
    }
}
