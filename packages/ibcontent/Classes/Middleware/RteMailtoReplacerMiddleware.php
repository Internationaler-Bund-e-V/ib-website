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
 * see MA -> 2555
 * Replaces mailto marker with obfuscated email + overlay
 */

class RteMailtoReplacerMiddleware implements MiddlewareInterface
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
            array(GeneralUtility::getFileAbsFileName('EXT:ibcontent/Resources/Private/Templates/MailtoReplacer'))
        );

        $content = preg_replace_callback(
            '(\###IBMAILTOSTART###.*?\###IBMAILTOEND###)',
            function ($match) {
                $match = str_replace(array('###IBMAILTOSTART###', '###IBMAILTOEND###'), '', $match);

                return $this->renderTagTemplate($match[0]);
            },
            $content
        );

        // push new content back into the response
        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }

    private function renderTagTemplate(mixed $options): string
    {
        $this->standaloneView->setTemplate('Mailto');
        $this->template = $this->standaloneView;
        $this->template->assignMultiple(array('mailto' => $options));

        return $this->template->render();
    }
}
