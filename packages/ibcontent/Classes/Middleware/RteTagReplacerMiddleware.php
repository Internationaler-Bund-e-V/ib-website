<?php

declare(strict_types=1);

namespace Ib\Ibcontent\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Replace global ib markers (i.e. ###IBSTART###YT#:#hTSGCeEk0uQ###IBEND###) in textcontent.
 * This replaces the old hook (GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][])
 *
 * see ibcontent/Resources/Private/Templates/TagReplacer
 * see ibcontent/Configuration/RequestMiddlewares.php
 * mk@rms, 2022-07-11
 */
class RteTagReplacerMiddleware implements MiddlewareInterface
{
    private StandaloneView $template;
    private StandaloneView $standaloneView;

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
        //set view paths
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);

        $this->standaloneView = $standaloneView;
        $this->standaloneView->setTemplateRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:ibcontent/Resources/Private/Templates/TagReplacer'))
        );

        $content = preg_replace_callback(
            '(\###IBSTART###.*?\###IBEND###)',
            function ($match) {
                //prepare string for usage as options
                $match = str_replace(array ('###IBSTART###', '###IBEND###'), '', $match);
                $options = explode('#:#', $match[0]);

                return $this->renderTagTemplate($options);
            },
            (string) $content
        );

        // push new content back into the response
        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }

    private function renderTagTemplate(array $options): string
    {
        //options[0] => YT, Generic, etc.
        switch ($options[0]) {
            case 'TAG':
                $this->standaloneView->setTemplate('GenericTag');
                $this->template = $this->standaloneView;
                $this->template->assignMultiple(array('genericTag' => $options));

                return $this->template->render();
            case 'YT':
                $this->standaloneView->setTemplate('YoutubeTag');
                $this->template = $this->standaloneView;
                $this->template->assignMultiple(array('youtubecode' => $options[1], 'startAt' => $options[2]));

                return $this->template->render();
            case 'IFRAME':
                $this->standaloneView->setTemplate('IframeTag');
                $this->template = $this->standaloneView;
                $this->template->assignMultiple(array('skripturl' => $options[1], 'customHeight' => $options[2]));

                return $this->template->render();
            default:
                return "";
        }
    }

    /**
     * extract YT code/url etc.
     * @return mixed
     */
    /*
    private function getCode(string $tag): mixed
    {
        if (preg_match('/###IBYT_(.*?)_IBYT###/', $tag, $match) == 1) {
            return $match[1];
        } else {
            return "err";
        }
    }
    */
}
