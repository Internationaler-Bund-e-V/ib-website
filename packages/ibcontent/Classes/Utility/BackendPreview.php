<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * @package  ibcontent
 * @see  http://t3-developer.com/extbase-fluid/backend-forms-module/eigene-backendmodule/fe-extension-im-backend-rendern/
 *
 * @see ibcontent/Classes/Utility/ContentElementLabelRenderer.php
 */
class BackendPreview extends ActionController
{
    /**
     * Function called backend view, used to generate preview of the plugin
     * @return string|void
     */
    public function renderPluginPreview(array $params, mixed &$pObj)
    {
        if ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_startpageslider') {
            return $this->previewStartPageSlider($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_bubbleslider') {
            return $this->previewBubbleSlider($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_breadcrump') {
            return $this->previewBreadcrump($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_locations') {
            return $this->previewLocations($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_jobs') {
            return $this->previewJobs($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_accordion') {
            return $this->previewAccordion($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_textextended') {
            return $this->previewTextExtended($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_sidebarmap') {
            return $this->previewSidebarMap($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_sidebardownloads') {
            return $this->previewSidebarDownloads($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_mediaelement') {
            return $this->previewMediaElement($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_contentslider') {
            return $this->previewContentSlider($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_textsliderextended') {
            return $this->previewTextSliderExtended($params['row']);
        } elseif ($params['row']['CType'] === 'list' && $params['row']['list_type'] === 'ibcontent_dbproductlist') {
            return $this->previewDBProductList($params['row']);
        }
    }

    /**
     * Render Interesting Facts Preview
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewStartPageSlider($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data['data']['sDEF']['lDEF']);
        $BEoutput = '<h4>IB Start Page Slider</h4><br>';
        $BEoutput .= 'Layout: ' . $data['data']['sDEF']['lDEF']['settings.layout']['vDEF'] . '<br><br><br>';

        if (is_array($data['data']['sDEF']['lDEF']['settings.sliderContainer']['el'])) {
            foreach ($data['data']['sDEF']['lDEF']['settings.sliderContainer']['el'] as $slide) {
                $BEoutput .= '<h5>Slide</h5>';
                $BEoutput .= '<img src="/' . $slide['ItemWrap']['el']['slideImage']['vDEF'] . '" width="250">';

                $BEoutput .= '<br><br>';
            }
        }

        return $BEoutput;
    }

    /**
     * Render Breadcrump
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewBreadcrump($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Breadcrump</h4><br>';

        return $BEoutput;
    }

    /**
     * Render Bubble Slider
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewBubbleSlider($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data['data']['rightSide']['lDEF']['settings.sliderContainer']['el']);
        $BEoutput = '<h4>IB Bubble Slider</h4><br>';

        if ($data['data']['sDEF']['lDEF']['settings.headline']) {
            $BEoutput .= $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '<br>';
        }

        foreach ($data['data']['rightSide']['lDEF']['settings.sliderContainer']['el'] as $slide) {
            $BEoutput .= '<h5>' . $slide['ItemWrap']['el']['headline']['vDEF'] . '</h5>';
            $BEoutput .= $slide['ItemWrap']['el']['text']['vDEF'] . '<br>';
            $BEoutput .= '<img src="/' . $slide['ItemWrap']['el']['slideImage']['vDEF'] . '" width="250">';

            $BEoutput .= '<br><br>';
        }

        return $BEoutput;
    }

    /**
     * Render Locations
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewLocations($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Locations</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';
        $BEoutput .= 'Lat: ' . $data['data']['sDEF']['lDEF']['settings.lat']['vDEF'] . '<br>';
        $BEoutput .= 'Long: ' . $data['data']['sDEF']['lDEF']['settings.long']['vDEF'] . '<br>';
        $BEoutput .= 'Zoom: ' . $data['data']['sDEF']['lDEF']['settings.zoom']['vDEF'] . '<br>';
        $BEoutput .= 'APIKey: ' . $data['data']['sDEF']['lDEF']['settings.apiKey']['vDEF'] . '<br>';

        return $BEoutput;
    }

    /**
     * Render Jobs
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewJobs($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Jobs</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';

        return $BEoutput;
    }

    /**
     * Render Accordion
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewAccordion($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data['data']['rightSide']['lDEF']['settings.sliderContainer']['el']);
        $BEoutput = '<h4>IB Accordion</h4><br>';

        if (isset($data['data']['rightSide']['lDEF']['settings.sliderContainer']['el'])) {
            foreach ($data['data']['rightSide']['lDEF']['settings.sliderContainer']['el'] as $slide) {
                $BEoutput .= '<h5>' . $slide['ItemWrap']['el']['title']['vDEF'] . '</h5>';
            }
        }

        return $BEoutput;
    }

    /**
     * Render Text Extended
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewTextExtended($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Text Extended</h4><br>';

        if (isset($data['data']['sDEF']['lDEF']['settings.array']['el'])) {
            foreach ($data['data']['sDEF']['lDEF']['settings.array']['el'] as $slide) {
                $BEoutput .= '<h5>' . $slide['ItemWrap']['el']['headline']['vDEF'] . '</h5>';
            }
        }

        return $BEoutput;
    }

    /**
     * Render Sidebar Map
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewSidebarMap($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Sidebar Map</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';
        $BEoutput .= $data['data']['sDEF']['lDEF']['settings.description']['vDEF'] . '<br>';
        //if ($slide['ItemWrap']['el']['slideImage']['vDEF'])
        //    $BEoutput .= '<img src="/' . $slide['ItemWrap']['el']['slideImage']['vDEF'] . '" width="250">';

        return $BEoutput;
    }

    /**
     * Render SidebarDownloads
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewSidebarDownloads($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Sidebar Downloads</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';

        foreach ($data['data']['rightSide']['lDEF']['settings.fileContainer']['el'] as $files) {
            $BEoutput .= '<h5>' . $files['ItemWrap']['el']['filename']['vDEF'] . '</h5>';
        }

        return $BEoutput;
    }

    /**
     * Render Media Element
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewMediaElement($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Media Element</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';
        $BEoutput .= 'Media Type: ' . $data['data']['rightSide']['lDEF']['settings.type']['vDEF'];

        if ($data['data']['rightSide']['lDEF']['settings.type']['vDEF'] == 'video') {
            if ($data['data']['rightSide']['lDEF']['settings.videoPoster']['vDEF']) {
                $BEoutput .= '<br><img src="/' . $data['data']['rightSide']['lDEF']['settings.videoPoster']['vDEF'] . '" width="250"><br>';
            }
        } elseif ($data['data']['rightSide']['lDEF']['settings.type']['vDEF'] == 'image') {
            if ($data['data']['rightSide']['lDEF']['settings.image']['vDEF']) {
                $BEoutput .= '<br><img src="/' . $data['data']['rightSide']['lDEF']['settings.image']['vDEF'] . '" width="250"><br>';
            }
        } elseif ($data['data']['rightSide']['lDEF']['settings.type']['vDEF'] == 'youtube') {
            $BEoutput .= '<br>ID: ' . $data['data']['rightSide']['lDEF']['settings.youtube']['vDEF'] . '<br>';
        }

        return $BEoutput;
    }

    /**
     * Render Content Slider
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewContentSlider($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Content Slider</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';

        if ($data['data']['sDEF']['lDEF']['settings.type']['vDEF'] == "image") {
            foreach ($data['data']['imageContent']['lDEF']['settings.imageSliderContainer']['el'] as $images) {
                $BEoutput .= '<img src="/' . $images['ItemWrap']['el']['image']['vDEF'] . '" width="200"><br>';
                $BEoutput .= $images['ItemWrap']['el']['caption']['vDEF'] . '<br>';
            }
        } elseif ($data['data']['sDEF']['lDEF']['settings.type']['vDEF'] == "leitsatz") {
            foreach ($data['data']['leitsatzContent']['lDEF']['settings.guidelineSliderContainer']['el'] as $leitsatz) {
                $BEoutput .= $leitsatz['ItemWrap']['el']['leitsatz']['vDEF'] . '<br><br><br>';
            }
        }

        return $BEoutput;
    }




    protected function previewTextSliderExtended($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB Content Slider</h4><br>';
        $BEoutput .= '<h5>' . $data['data']['sDEF']['lDEF']['settings.headline']['vDEF'] . '</h5>';

        if (isset($data['data']['sDEF']['lDEF']['settings.array']['el'])) {
            foreach ($data['data']['sDEF']['lDEF']['settings.array']['el'] as $slide) {
                $BEoutput .= '<h5>' . $slide['ItemWrap']['el']['headline']['vDEF'] . '</h5>';
            }
        }

        // if ($data['data']['sDEF']['lDEF']['settings.type']['vDEF'] == "image") {
        //     foreach ($data['data']['imageContent']['lDEF']['settings.imageSliderContainer']['el'] as $images) {
        //         $BEoutput .= '<img src="/' . $images['ItemWrap']['el']['image']['vDEF'] . '" width="200"><br>';
        //         $BEoutput .= $images['ItemWrap']['el']['caption']['vDEF'] . '<br>';
        //     }
        // } elseif ($data['data']['sDEF']['lDEF']['settings.type']['vDEF'] == "leitsatz") {
        //     foreach ($data['data']['leitsatzContent']['lDEF']['settings.guidelineSliderContainer']['el'] as $leitsatz) {
        //         $BEoutput .= $leitsatz['ItemWrap']['el']['leitsatz']['vDEF'] . '<br><br><br>';
        //     }
        // }

        return $BEoutput;
    }

    /**
     * Render DBProductList
     *
     * @param array $row tt_content row of the plugin
     * @return string rendered preview html
     */
    protected function previewDBProductList($row)
    {
        $this->initializeAction();
        $data = GeneralUtility::xml2array($row['pi_flexform']);
        // $this->showDebugger($data);
        $BEoutput = '<h4>IB DBProductList</h4><br>';

        return $BEoutput;
    }

    /**
     * shows debugger for an array - helps at development
     * @param  array
     * @return void
     */
    /*
    private function showDebugger(array $array)
    {
        DebuggerUtility::var_dump($array);
    }
    */

    /**
     * helper function - shorten string to a max length
     */
    protected function shortenString(string $string, int $maxlength = 50): string
    {
        return mb_strimwidth((string)$string, 0, $maxlength, "...");
    }
}
