<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ContactFormController extends ActionController
{
    private array $customSettings;

    public function __construct()
    {
        //parent::__construct();
        $this->getSettings();
    }

    public function contactFormAction(): ResponseInterface
    {
        $this->view->assignMultiple(array(
            'uid' => $this->configurationManager->getContentObject()->data['uid'],
            'customSettings' => $this->customSettings,
        ));

        return $this->htmlResponse();
    }

    public function submitContactFormAction(): ResponseInterface
    {
        // new implementation MA#2064 friendlyRecaptcha
        $mailMsg = htmlentities((string)GeneralUtility::_GP('msg'));
        $mailFrom = htmlentities((string)GeneralUtility::_GP('eMail'));
        $mailSalutation = htmlentities((string)GeneralUtility::_GP('salutation'));
        $mailPhone = htmlentities((string)GeneralUtility::_GP('phone'));
        $mailFirstName = htmlentities((string)GeneralUtility::_GP('firstName'));
        $mailLastName = htmlentities((string)GeneralUtility::_GP('lastName'));
        $mailRecaptcha = htmlentities((string)GeneralUtility::_GP('frc-captcha-solution'));

        if (GeneralUtility::validEmail($mailFrom) && $this->getRecaptchaResponse($mailRecaptcha)) {
            /** @var MailMessage $mail */
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $flexConfig = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

            $mail->setSubject('Kontaktformular');
            //set custom subject if set
            if (!empty($flexConfig['subject'])) {
                $mail->setSubject($flexConfig['subject']);
            }

            //$mail->setFrom(array( $mailFrom => $mailFirstName." ".$mailLastName));
            $mail->setFrom(array($this->customSettings['emailFrom'] => 'Kontaktformular'));
            $mail->setReplyTo(array($mailFrom));
            //$mail->setTo($this->getReceiver());
            //MA#772
            $mailAddresses = explode(",", (string)$this->settings['emailreceiver']);
            $mail->setTo($mailAddresses);

            $content =
                '<html>' .
                'Anrede: ' . $mailSalutation . '<br>' .
                'Name:' . $mailFirstName . ' ' . $mailLastName . '<br>' .
                'eMail: ' . $mailFrom . '<br>' .
                'Telefon: ' . $mailPhone . '<br><br>' .
                'Nachricht:' . '<br>' .
                $mailMsg . '<br><br>' .
                'Diese Email wurde automatisch generiert. Bitte antworten Sie nicht auf den Absender!' .
                '</html>';

            $mail->setBody()->html($content);
            $mail->send();
        }

        return $this->htmlResponse();
    }

    private function getSettings(): void
    {
        //$this->customSettings = $confArray = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"][strtolower($this->extensionName)]);
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->customSettings = $extConf->get('ibcontent');
        //$this->baseURL = $this->customSettings['urlIBPdb'];
        //$this->interfaceURL = $this->customSettings['urlIBPdbInteface'];
        //$this->imageURL = $this->customSettings['urlIBPdbImages'];
    }

    private function getRecaptchaResponse(mixed $form_response): string|bool
    {
        $requestURL = "https://api.friendlycaptcha.com/api/v1/siteverify";
        $re_secret = $this->customSettings['reCaptchaCode'];

        $result = [];
        if ($form_response === null) {
            return false;
        }

        $postdata = http_build_query(
            array(
                'solution' => $form_response,
                'secret' => $re_secret,
            )
        );

        $opts = array(
            'http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
            ),
        );

        $context = stream_context_create($opts);
        $result = json_decode((string)file_get_contents($requestURL, false, $context), true);

        return $result['success'];
    }

    /*
    private function getReceiver(): array
    {
        $receiversExplode = explode(",", (string)$this->settings['emailreceiver']);
        $rArray = array();

        foreach ($receiversExplode as $r) {
            $rArray[$r] = $r;
        }

        return $rArray;
    }
    */
}
