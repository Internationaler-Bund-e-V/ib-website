<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Seo;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Seo\XmlSitemap\AbstractXmlSitemapDataProvider;

class IbXmlSitemapDataProvider extends AbstractXmlSitemapDataProvider
{
    public function __construct(ServerRequestInterface $request, string $key, array $config = [], ContentObjectRenderer $cObj = null)
    {
        parent::__construct($request, $key, $config, $cObj);

        $this->key = $key;
        $this->config = $config;
        $this->request = $request;

        //if ($cObj === null) {
        //    $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        //}
        //$this->cObj = $cObj;

        $this->numberOfItemsPerPage = 2000;
        $this->generateItems();
    }

    public function generateItems(): void
    {
        if ($this->config['entity'] == 'Location') {
            $this->generateLocations();
        } else {
            $this->generateFWD();
        }
    }

    protected function defineUrl(array $data): array
    {
        $pageId = $this->config['url']['pageId'] ?? $GLOBALS['TSFE']->id;
        $typoLinkConfig = [
            'parameter' => $pageId,
            'additionalParams' => '&' . $data['data']['urlParameter'] . '=' . $data['data']['id'],
            'forceAbsoluteUrl' => 1,
        ];
        $data['loc'] = $this->cObj->typoLink_URL($typoLinkConfig);

        return $data;
    }

    /**
     * generate location sitemap
     */
    private function generateLocations(): void
    {
        //DebuggerUtility::var_dump($this->config);
        //die();
        $navID = $this->config['navigationID'];
        $redaktionstoolURL = $this->config['redaktionURL'];

        $locations = file_get_contents($redaktionstoolURL . '/interfaces/requestLocationsList/nav_id:' . $navID);
        $locations = json_decode((string)$locations, true);

        foreach ($locations['locations'] as $location) {
            $data = [
                'loc' => '/standorte/' . $location['Location']['id'],
                'lastMod' => $location['Location']['modified'],
                'data' => [
                    'id' => $location['Location']['id'],
                    'urlParameter' => 'tx_ibcontent[lid]',
                ],

            ];
            $this->items[] = $this->defineUrl($data);
        }
    }
    /**
     * generate FWD sitemap
     */
    private function generateFWD(): void
    {
        $redaktionstoolURL = $this->config['redaktionURL'];

        $fwds = file_get_contents($redaktionstoolURL . '/interfaces/requestJobsList');
        $fwds = json_decode((string)$fwds);

        foreach ($fwds as $key => $value) {
            $data = [
                'lastMod' => $value,
                'data' => [
                    'id' => $key,
                    'urlParameter' => 'tx_ibcontent[jid]',
                ],
            ];
            $this->items[] = $this->defineUrl($data);
        }
    }
}
