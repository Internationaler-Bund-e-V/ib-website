<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3\CMS\Seo\Event\ModifyUrlForCanonicalTagEvent;

class MyContentController extends ActionController
{
    private array $customSettings;
    private string $interfaceURL;
    //private readonly string $imgURL;
    //private string $baseURL;
    //private string $imageURL;

    /**
     * @var TypoScriptFrontendController|null
     */
    protected $typoScriptFrontendController;

    public function __construct()
    {
        $this->typoScriptFrontendController = $GLOBALS['TSFE'] ?? null;
    }

    protected function initializeAction(): void
    {
        $this->getSettings();
    }

    public function startPageSliderAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function bubbleSliderAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function accordionAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function textExtendedAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function jobsAction(): ResponseInterface
    {
        $jobs = array(
            [
                'headline' => '11Lorem Larem Ipsum (M/W)',
                'text' => '11lPraesent placerat pellentesque tincidunt. Sed dolor massa, interdum ut varius ut, malesuada sit amet dolor. Nam nec malesuada justo. Cras tincidunt nunc luctus libero sce',
                'link' => 1,
            ],
            [
                'headline' => '22Lorem Larem Ipsum (M/W)',
                'text' => '22lPraesent placerat pellentesque tincidunt. Sed dolor massa, interdum ut varius ut, malesuada sit amet dolor. Nam nec malesuada justo. Cras tincidunt nunc luctus libero sce',
                'link' => 1,
            ],
            [
                'headline' => '33Lorem Larem Ipsum (M/W)',
                'text' => '33lPraesent placerat pellentesque tincidunt. Sed dolor massa, interdum ut varius ut, malesuada sit amet dolor. Nam nec malesuada justo. Cras tincidunt nunc luctus libero sce',
                'link' => 1,
            ],
            [
                'headline' => '44Lorem Larem Ipsum (M/W)',
                'text' => '44lPraesent placerat pellentesque tincidunt. Sed dolor massa, interdum ut varius ut, malesuada sit amet dolor. Nam nec malesuada justo. Cras tincidunt nunc luctus libero sce',
                'link' => 1,
            ],
        );

        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);
        $this->view->assign('jobs', $jobs);

        return $this->htmlResponse();
    }

    public function breadcrumpAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function sidebarMapAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function sidebarDownloadsAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function mediaElementAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function contentSliderAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function textSliderExtendedAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function tilesAction(): ResponseInterface
    {
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    public function osmShowMapAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function osmShowListAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * flocklr social wall
     */
    public function swShowAction(): ResponseInterface
    {
        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
            ),
        );

        return $this->htmlResponse();
    }

    /**
     * fundraising
     */
    public function frShowAction(): ResponseInterface
    {
        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
            )
        );

        return $this->htmlResponse();
    }

    /**
     * raisenow
     */
    public function rnShowAction(): ResponseInterface
    {
        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
            )
        );

        return $this->htmlResponse();
    }

    public function dbShowJoblistAction(): ResponseInterface
    {
        if (!isset($this->settings['dbportalID'])) {
            $this->settings['dbportalID'] = 0;
        }

        $cities = urlencode((string) $this->settings['dbcities']);
        $jobsByCity = $this->getURL($this->interfaceURL . "/requestJobs/portalid:" . $this->settings['dbportalID'] . "/cities:" . $cities);
        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'jobsByCity' => json_decode((string) $jobsByCity, true),
                'settings' => $this->settings,
            )
        );

        return $this->htmlResponse();
    }

    public function dbShowJobAction(): ResponseInterface
    {
        $extVars = $this->request->getQueryParams()['tx_ibcontent'] ?? null;
        $job = json_decode((string) $this->getUrl($this->interfaceURL . "/requestJob/id:" . intval($extVars['jid'])), true);

        $this->setTags('IB Freiwilligendienste | ' . $job['Job']['name'], $job['Job']['name'], 'job', intval($extVars['jid']));

        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'job' => $job,
                'customSettings' => $this->customSettings,
                'settings' => $this->settings,
            )
        );

        return $this->htmlResponse();
    }

    public function dbShowForeignjobAction(): ResponseInterface
    {
        $extVars = $this->request->getQueryParams()['tx_ibcontent'] ?? null;
        $job = json_decode((string) $this->getUrl($this->interfaceURL . "/requestForeignjob/id:" . intval($extVars['fjid'])), true);

        $this->setTags('IB Freiwilligendienste | ' . $job['Job']['name'], $job['Job']['name'], 'foreignJob', intval($extVars['fjid']));

        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'job' => $job,
                'customSettings' => $this->customSettings,
                'settings' => $this->settings,
            )
        );

        return $this->htmlResponse();
    }

    public function dbShowNewsAction(): ResponseInterface
    {
        $extVars = $this->request->getQueryParams()['tx_ibcontent'] ?? null;
        $newsarticle = json_decode(
            (string) $this->getUrl($this->interfaceURL . "/requestNewsarticle/id:" . intval($extVars['nid'])),
            true
        );
        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'newsarticle' => $newsarticle,
                'customSettings' => $this->customSettings,
                'settings' => $this->settings,
            )
        );

        return $this->htmlResponse();
    }

    public function dbShowCategoryAction(): ResponseInterface
    {
        $letterArray = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
        );
        $productsByCity = $this->getURL($this->interfaceURL . "/requestCategory/id:" . urlencode((string) $this->settings['dbcategoryid']) . "/navigation:" . $this->settings['navigationID']);

        $this->view->assignMultiple(
            array(
                'letterArray' => $letterArray,
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'productsByCity' => json_decode((string) $productsByCity, true),
                'settings' => $this->settings,
            )
        );

        return $this->htmlResponse();
    }

    public function dbShowProductAction(): ResponseInterface
    {
        $extVars = $this->request->getQueryParams()['tx_ibcontent'] ?? null;

        // check if there is a singleview id set in the flexforms configuration
        $aid = 0;

        if (!isset($extVars)) {
            if (isset($this->settings['singleProductIdToShow'])) {
                $aid = (int) $this->settings['singleProductIdToShow'];
            }
            $extVars = [
                'aid' => $aid,
            ];
        }
        //DebuggerUtility::var_dump($this->settings['singleProductIdToShow']);
        //die();
        $product = json_decode((string) $this->getURL($this->interfaceURL . "/requestProduct/id:" . (int) $extVars['aid']), true);

        //$this->setTags('IB Angebot | ' . $product['Product']['name'], $product['Product']['short_description']); -> disable due to MA#2041
        $this->setTags('IB Angebot | ' . $product['Product']['name'], $product['Product']['name'], 'product', intval($extVars['aid']));

        $this->view->assignMultiple(
            array(
                'product_id' => intval($extVars['aid']),
                'product' => $product,
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'show' => array(
                    'process' => 'Der Ablauf',
                    'prerequisites' => 'Die Voraussetzungen',
                    'target_group' => 'Zielgruppe',
                    'goal' => 'Die Ziele des Angebots',
                    'training_hours' => 'Unterrichtszeiten',
                ),
                'customSettings' => $this->customSettings,
                'settings' => $this->settings,
                //'news'                => $this->getNews($product['Product']['news'])

            )
        );

        return $this->htmlResponse();
    }

    public function dbShowLocationAction(): ResponseInterface
    {
        $extVars = $this->request->getQueryParams()['tx_ibcontent'] ?? null;

        // check if there is a singleview id set in the flexforms configuration
        $lid = 0;

        if (!isset($extVars)) {
            if (isset($this->settings['singleLocationIdToShow'])) {
                $lid = (int) $this->settings['singleLocationIdToShow'];
            }
            $extVars = [
                'lid' => $lid,
            ];
        }

        //check if preview -> ignore status
        $preview = false;
        if (isset($extVars['preview']) && $extVars['preview'] === 'true') {
            $preview = 'true';
        }

        $location = json_decode(
            (string) $this->getUrl($this->interfaceURL . "/requestLocation/id:" . (int) $extVars['lid'] . "/preview:" . $preview),
            true
        );

        //Seo set custom meta tags
        $customPageTitle = $location['Location']['name'];
        //$customPageTitle = $location['Location']['short_description']; -> see MA#2041 & MA&2095
        $customPageTitle = $customPageTitle;
        if (!empty($location['Location']['meta_title'])) {
            $customPageTitle = $location['Location']['meta_title'];
        }
        $customMetaDescription = "";
        if (!empty($location['Location']['meta_description'])) {
            $customMetaDescription = $location['Location']['meta_description'];
        }

        //set headerTags
        $this->setTags('IB Standort | ' . $customPageTitle, $customMetaDescription, 'location', intval($extVars['lid']));

        $this->view->assignMultiple(
            array(
                'location_id' => intval($extVars['lid']),
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'location' => $location,
                'customSettings' => $this->customSettings,
                'settings' => $this->settings,
                //'news'                => $this->getNews($location['Location']['news'])

            )
        );

        return $this->htmlResponse();
    }

    private function getSettings(): void
    {
        //$this->customSettings = $confArray = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"][strtolower($this->extensionName)]);
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->customSettings = $extConf->get('ibcontent');
        //$this->baseURL = $this->customSettings['urlIBPdb'];
        $this->interfaceURL = $this->customSettings['urlIBPdbInteface'];
        //$this->imageURL = $this->customSettings['urlIBPdbImages'];
    }

    private function getURL(string $url): bool|string
    {
        $session = curl_init($url);

        if (!$session) {
            throw new \Exception('Curl init failed');
        }

        // Don't return HTTP headers. Do return the contents of the call
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        //debug(curl_exec($session));die();
        if (curl_exec($session) == "err101") {
            //$GLOBALS['TSFE']->pageNotFoundAndExit($this->entityNotFoundMessage);
            /** @var ErrorController $errorController */
            $errorController = GeneralUtility::makeInstance(ErrorController::class);
            $response = $errorController->pageNotFoundAction(
                $GLOBALS['TYPO3_REQUEST'],
                'Your error message',
                ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
            );
            throw new ImmediateResponseException($response);
        } else {
            return curl_exec($session);
        }
    }

    /**
     * set custom pagetitel and description
     * see layout.ts for page.headerdata
     */
    private function setTags(string $title, string $description, string $type = "", int $canonUrlID = 0): void
    {

        $canonicalURLs = [
            'location' => 'https://internationaler-bund.de/standort/',
            'product' => 'https://internationaler-bund.de/angebot/',
            'job' => 'https://ib-freiwilligendienste.de/job/',
            'foreignJob' => 'https://ib-freiwilligendienste.de/job-ausland/',
        ];

        $GLOBALS['TSFE']->page['title'] = $title;

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        //set page title
        $pageRenderer->setTitle($title);

        //no description -> desc = title
        if (empty($description)) {
            $description = $title;
        }

        //modify canonical tag -> MA#2512
        $canonical = '<link ' . GeneralUtility::implodeAttributes([
            'rel' => 'canonical',
            'href' => $canonicalURLs[$type] . $canonUrlID,
        ], true) . '/>' . chr(10);

        if ($this->typoScriptFrontendController->additionalHeaderData !== []) {
            foreach ($this->typoScriptFrontendController->additionalHeaderData as $key => $value) {
                if (str_contains($value, 'rel="canonical"')) {
                    $this->typoScriptFrontendController->additionalHeaderData[$key] = $canonical;
                }
            }
        }
        /////////////////////////////////
        //replace robot tags -> always INDEX,FOLLOW -> see https://mantis.rm-solutions.de/mantis/view.php?id=2319#c7934
        /** @var MetaTagManagerRegistry $metaTagManagerRegistry */
        $metaTagManagerRegistry = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        //robots
        $metaTagManager = $metaTagManagerRegistry->getManagerForProperty('robots');
        $metaTagManager->removeProperty('robots');
        $metaTagManager->addProperty('robots', 'INDEX,FOLLOW');
        //add meta description
        $description = substr(htmlentities(strip_tags(trim((string) $description)), ENT_QUOTES, 'utf-8'), 0, 150);
        $metaTagManager = $metaTagManagerRegistry->getManagerForProperty('description');
        $metaTagManager->removeProperty('description');
        $metaTagManager->addProperty('description', $description);
    }
}
