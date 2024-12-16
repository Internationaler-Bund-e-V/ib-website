<?php

declare(strict_types=1);

namespace Rms\IbGalerie\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rms\IbGalerie\Domain\Repository\GalerieRepository;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Replace gallery markers in textcontent with the real gallery code.
 * This replaces the old hook (GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][])
 *
 * see ib_galerie/Resources/Private/Backend/Templates/Galerie/Fe.html
 * see ib_galerie/Configuration/RequestMiddlewares.php
 * mk@rms, 2022-07-11
 */
class GalleryReplacerMiddleware implements MiddlewareInterface
{
    /** @var int $i */
    private int $i = 0;

    /** @var GalerieRepository $galerieRepository */
    private GalerieRepository $galerieRepository;

    /** @var StandaloneView $template */
    private StandaloneView $template;

    /** @var array $extbaseFrameworkConfiguration */
    private array $extbaseFrameworkConfiguration = [];

    private FrontendInterface $cache;

    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    protected function getCachedValue(mixed $match): mixed
    {
        $cacheIdentifier = "ib_galerie_" . md5($match[0]);

        // If $entry is false, it hasn't been cached. Calculate the value and store it in the cache:
        $value = $this->cache->get($cacheIdentifier);
        if ($value === false) {
            $value = $this->galerieRepository->findByCode($match[0]);
            $tags = [];
            $lifetime = "";

            // Save value in cache
            $this->cache->set($cacheIdentifier, $value, $tags);
        }

        return $value;
    }

    public function injectGalleryRepository(GalerieRepository $galerieRepository): void
    {
        $this->galerieRepository = $galerieRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // let it generate a response
        $response = $handler->handle($request);
        if ($response instanceof NullResponse) {
            return $response;
        }

        // extract the content
        $body = $response->getBody();
        $body->rewind();

        $content = $response->getBody()->getContents();

        // ------------------------------------------------
        // the actual replacement
        // ------------------------------------------------
        //$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        //$standaloneView = $objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);

        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);

        //$cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        //$this->cacheInstance = $cacheManager->getCache('ib_galerie');

        $standaloneView->setLayoutRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:ib_galerie/Resources/Private/Backend/Layouts'))
        );
        $standaloneView->setPartialRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:ib_galerie/Resources/Private/Backend/Partials'))
        );
        $standaloneView->setTemplateRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:ib_galerie/Resources/Private/Backend/Templates'))
        );
        $standaloneView->setTemplate('Galerie/Fe');

        $this->template = $standaloneView;

        //$this->configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');

        /** @var ConfigurationManager $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        // Get/Set the default Settings for portal -  pid/galery - folder
        $pid = 0;
        if (isset($this->extbaseFrameworkConfiguration['module.']['tx_ibgalerie_web_ibgalerieibgaleriebe.']['persistence.']['storagePid'])) {
            $pid = (int) $this->extbaseFrameworkConfiguration['module.']['tx_ibgalerie_web_ibgalerieibgaleriebe.']['persistence.']['storagePid'];
        }
        //$querySettings = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setStoragePageIds($this->getTreePids($pid));

        //get gallery repository
        //$this->galerieRepository = $objectManager->get('Rms\\IbGalerie\\Domain\\Repository\\GalerieRepository');
        $this->galerieRepository->setDefaultQuerySettings($querySettings);
        //replace code with gallery

        $content = preg_replace_callback(
            '/###IBG.*###/',
            function ($match) {
                /*
                $cacheIdentifier = md5($match[0]);
                //$this->cacheInstance->flushCaches();
                if ($this->cacheInstance->has($cacheIdentifier)) {

                    $galerie = $this->cacheInstance->get($cacheIdentifier);
                    //$galerie = $this->galerieRepository->findByCode($match);
                    //DebuggerUtility::var_dump($galerie);
                    //print_r($galerie);
                } else {
                    $galerie = $this->galerieRepository->findByCode($match);
                    $this->cacheInstance->set($cacheIdentifier, $galerie);
                }
                */

                //$galerie = $this->galerieRepository->findByCode($match[0]);
                $galerie = $this->getCachedValue($match);
                //\debug($galerie);
                $this->template->assignMultiple(
                    array(
                        'galerie' => $galerie, //'galerie' => $galerie[0],
                        'counter' => $this->i,
                    )
                );
                $this->i++;

                return $this->template->render();
            },
            (string) $content
        );

        // push new content back into the response
        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }

    /**
     * get all folder/pids recursively from
     * module.tx_ibgalerie_web_ibgalerieibgaleriebe.persistence.storagePid = xxxx
     */
    private function getTreePids(int $parent = 0): array
    {
        $depth = 999999;
        /** @var QueryGenerator $queryGenerator */
        $queryGenerator = GeneralUtility::makeInstance(QueryGenerator::class);
        $childPidsString = $queryGenerator->getTreeList($parent, $depth, 0, "1"); //Will be a string like 1,2,3
        $childPids = explode(',', $childPidsString);

        return $childPids;
    }
}
