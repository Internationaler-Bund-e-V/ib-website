<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class renders a human readable title for FCEs,
 * so one is able to find a content element by its headline.
 *
 * @see ibcontent/Classes/Utility/BackendPreview.php
 */
class ContentElementLabelRenderer implements SingletonInterface
{
    protected FlexFormService $flexFormService;

    public function injectFlexFormService(FlexFormService $flexFormService): void
    {
        $this->flexFormService = $flexFormService;
    }

    /**
     * Returns the content element title for a given content element
     * @param $params
     * @param $parentObject
     */
    public function getContentElementTitle(array &$params, mixed $parentObject): void
    {
        //\debug($params);
        switch ($params['row']['list_type']) {
            case 'ibcontent_startpageslider':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewStartPageSlider($record);
                }
                break;
            case 'ibsearch_searchform':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                $params['title'] = $this->previewIbSearchForm($record);
                break;
            case 'ibcontent_accordion':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewAccordion($record);
                }
                break;
            case 'ibcontent_textextended':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewTextExtended($record);
                }
                break;

            case 'ibcontent_bubbleslider':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewBubbleSlider($record);
                }
                break;

            case 'ibcontent_jobs':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewJobs($record);
                }
                break;

            case 'ibsearch_search':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewIbSearch($record);
                }
                break;

            case 'ibcontent_mediaelement':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewMediaelement($record);
                }
                break;

            case 'ibjobs_iblogajobs':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewIBLigaListJobs($record);
                }
                break;

            case 'ibcontent_breadcrump':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewBreadcrump($record);
                }
                break;

            case 'ibcontent_tiles':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewTiles($record);
                }
                break;

            case 'ibcontent_dbproductlist':
                $record = BackendUtility::getRecord($params['table'], $params['row']['uid']);
                if (\is_array($record)) {
                    $params['title'] = $this->previewDbContentProductlist($record);
                }
                break;

            default:
                $params['title'] = $params['row']['header'] ?: ($params['row']['subheader'] ?: $params['row']['bodytext']);

                if (isset($params['row']['list_type']) && !is_array($params['row']['list_type'])) {
                    if (strpos((string)$params['row']['list_type'], 'ibcontent_') !== false) {
                        $params['title'] = $params['row']['list_type'];
                    }
                }
                break;
        }
    }

    /**
     * Tiles
     *
     * @param array $record
     * @return string
     */
    private function previewTiles(array $record)
    {
        $title = 'IB Tiles ';

        $data = GeneralUtility::xml2array($record['pi_flexform']);
        //debug($data['data']['sDEF']['lDEF']);
        if (isset($data['data']['sDEF']['lDEF']['settings.headline']['vDEF'])) {
            $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'];
        }

        return $title;
    }

    /**
     * DB Product List
     *
     * @param array $record
     * @return string
     */
    private function previewDbContentProductlist(array $record)
    {
        $title = 'IB Product List Module';

        return $title;
    }

    /**
     * Breadcrump
     *
     * @param array $record
     * @return string
     */
    private function previewBreadcrump(array $record)
    {
        $title = 'IB Breadcrump ';

        return $title;
    }

    /**
     * IB Loga List Jobs
     *
     * @param array $record
     * @return string
     */
    private function previewIBLigaListJobs(array $record)
    {
        //$data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Loga List Jobs';

        return $title;
    }

    /**
     * Media Element
     *
     * @param array $record
     * @return string
     */
    private function previewMediaelement(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Media Element ';
        //debug($data['data']);
        //if (isset($data['data']['sDEF']['lDEF']['settings.headline']['vDEF'])) {
        //    $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'];
        //}
        if (isset($data['data']['rightSide']['lDEF']['settings.type']['vDEF'])) {
            $title .= ' - Typ: ' . $data['data']['rightSide']['lDEF']['settings.type']['vDEF'];
        }

        return $title;
    }

    /**
     * IB Search
     *
     * @param array $record
     * @return string
     */
    private function previewIbSearch(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Search ';
        //debug($data['data']['sDEF']['lDEF']);
        if (isset($data['data']['sDEF']['lDEF']['settings.navigation_id']['vDEF'])) {
            $title .= ' - Navigation ID: ' . $data['data']['sDEF']['lDEF']['settings.navigation_id']['vDEF'];
        }

        return $title;
    }

    /**
     * Jobs
     *
     * @param array $record
     * @return string
     */
    private function previewJobs(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Jobs ';
        if (isset($data['data']['sDEF']['lDEF']['settings.headline']['vDEF'])) {
            $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'];
        }

        return $title;
    }
    
    /**
     * Accordion
     *
     * @param array $record
     * @return string
     */
    private function previewBubbleSlider(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Bubble Slider ';
        if (isset($data['data']['sDEF']['lDEF']['settings.headline']['vDEF'])) {
            $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'];
        }

        return $title;
    }

    /**
     * Text Extended
     *
     * @param array $record
     * @return string
     */
    private function previewTextExtended(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Text Extended ';
        if (isset($data['data']['sDEF']['lDEF']['settings.array']['el'][1]['ItemWrap']['el']['headline']['vDEF'])) {
            $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.array']['el'][1]['ItemWrap']['el']['headline']['vDEF'];
        }

        return $title;
    }

    /**
     * Accordion
     *
     * @param array $record
     * @return string
     */
    private function previewAccordion(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Accordion ';
        if (isset($data['data']['sDEF']['lDEF']['settings.headline']['vDEF'])) {
            $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'];
        }

        return $title;
    }

    /**
     * IB Search Form
     *
     * @param array $record
     * @return string
     */
    private function previewIbSearchForm(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Search Form ';
        if (isset($data['data']['sDEF']['lDEF']['settings.headline']['vDEF'])) {
            $title .= ' - ' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'];
        }

        return $title;
    }

    /**
     * Startpage Slider
     *
     * @param array $record
     * @return string
     */
    private function previewStartPageSlider(array $record)
    {
        $data = GeneralUtility::xml2array($record['pi_flexform']);
        $title = 'IB Startpage Slider ';
        if (isset($data['data'])) {
            if ($record['rowDescription']) {
                $title .= ' - ' . substr(strip_tags((string)$record['rowDescription']), 0, 50);
            }
            //$title .= ' - (Slides: ' . count($data['data']['sDEF']['lDEF']['settings.sliderContainer']['el']) . ') ';
        }

        return $title;
    }
}
