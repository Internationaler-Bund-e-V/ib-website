<?php

declare(strict_types=1);

namespace Rms\Ibjobs\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Controller\ErrorController;

class JobsController extends ActionController
{
    /**
     * job frontend action
     */
    public function showAction(): ResponseInterface
    {
        if ($this->settings['prefilter']) {
            $jobs = json_decode(
                (string) $this->getUrl(
                    $this->settings['productDbBaseUrl'] . "interfaces/requestIbjobs/clients:" . $this->settings['clients'] .
                    "/sr_clients:" . $this->settings['sr_clients'] .
                    "/intern:" . $this->settings['intern'] .
                    "/locations:" . $this->settings['locations'] .
                    "/categories:" . $this->settings['categories'] .
                    "/titles:" . $this->settings['titles']
                )
            );
        } else {
            $jobs = json_decode((string) $this->getUrl(
                $this->settings['productDbBaseUrl'] . "interfaces/requestIbjobs/clients:" . $this->settings['clients'] . "/intern:" . $this->settings['intern'] . "/sr_clients:" . $this->settings['srclients']
            ));
        }

        $this->view->assignMultiple(
            array(
                'uid' => $this->request->getAttribute('currentContentObject')->data['uid'],
                'jobs' => $jobs,
                'settings' => $this->settings,
            )
        );

        return $this->htmlResponse();
    }

    private function getURL(string $url): string|bool
    {
        $session = curl_init($url);

        if (!$session) {
            return false;
        }

        // Don't return HTTP headers. Do return the contents of the call
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        if (curl_exec($session) == "err101") {
            if (!$this->settings['useCustomRedirect']) {
                /** @var ErrorController $errorctrl */
                $errorctrl = GeneralUtility::makeInstance(ErrorController::class);
                $response = $errorctrl->pageNotFoundAction(
                    $GLOBALS['TYPO3_REQUEST'],
                    'The requested page does not exist'
                );
            } else {
                $uriBuilder = $this->uriBuilder;
                $uri = $uriBuilder
                    ->setTargetPageUid($this->settings['customRedirectPageID'])
                    ->build();
                $response = new RedirectResponse($uri, 301);
            }
            throw new ImmediateResponseException($response, 1591428020);
        } else {
            return curl_exec($session);
        }
    }
}
