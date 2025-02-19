<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class AjaxController extends ActionController
{
    private readonly mixed $streamContext;
    private readonly array $custom_settings;

    public function __construct()
    {
        //parent::__construct();
        $this->streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        //$this->custom_settings = unserialize( $GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"][ strtolower( $this->extensionName ) ] );
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->custom_settings = $extConf->get('ibcontent');
    }

    /**
     * This function handles the submit on the contactform inside locations or products
     * on pages like https://www.internationaler-bund.de/angebot/5746/
     * or https://www.internationaler-bund.de/standort/209199/
     *
     * @author mkettel, 2017-04-24
     */
    public function submitLocationContactFormAction(): ResponseInterface
    {
        $errors = [];
        $data = $_POST;
        $parsedBody = is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : [];
        $queryParams = is_array($this->request->getQueryParams()) ? $this->request->getQueryParams() : [];

        $mailRecaptcha = $parsedBody['frc-captcha-solution'] ?? $queryParams['frc-captcha-solution'] ?? null;
        //DebuggerUtility::var_dump($data);die();
        // validate user input
        if (!in_array($data['salutation'], ['Herr', 'Frau', 'Keine Angabe', 'Neutrale Anrede'])) {
            $errors['salutation'] = true;
        }
        if (empty($data['full_name'])) {
            $errors['full_name'] = true;
        }
        if (empty($data['email'])) {
            $errors['email'] = true;
        }
        if (empty($data['message'])) {
            $errors['message'] = true;
        }
        if (!$this->getRecaptchaResponse($mailRecaptcha)) {
            $errors['captcha'] = true;
        }

        // get email address from pdb interface
        $item_id = intval($data['item_id']);
        $receiver = null;
        $subject = "Kontaktformular";
        if ($data['form_type'] == 'product') {
            // https://redaktion.internationaler-bund.de/interfaces/requestProductContact/id:9065/sToken:23048lsdjfiejf33j3kdji
            $xurl = $this->custom_settings['urlIBPdbInteface'] . '/requestProductContact/id:' . $item_id . '/sToken:23048lsdjfiejf33j3kdji';
            $receiver = file_get_contents($xurl, false, $this->streamContext);
            $subject = "Kontaktformular Angebot " . $item_id;
        }
        if ($data['form_type'] == 'location') {
            // https://redaktion.internationaler-bund.de/interfaces/requestProductContact/id:9065/sToken:23048lsdjfiejf33j3kdji
            $xurl = $this->custom_settings['urlIBPdbInteface'] . '/requestLocationContact/id:' . $item_id . '/sToken:23048lsdjfiejf33j3kdji';
            $receiver = file_get_contents($xurl, false, $this->streamContext);
            $subject = "Kontaktformular Standort " . $item_id;
        }

        // send mail
        if ($receiver && empty($errors)) {
            $message = '<br><b>Unternehmen:</b><br> ' . htmlspecialchars((string) $data['company']) . '<br>' .
                '<br><b>Anrede:</b><br> ' . htmlspecialchars((string) $data['salutation']) . '<br>' .
                '<br><b>Name, Vorname:</b><br> ' . htmlspecialchars((string) $data['full_name']) . '<br>' .
                '<br><b>Adresse:</b><br> ' . htmlspecialchars((string) $data['full_address']) . '<br>' .
                '<br><b>Email:</b><br> ' . htmlspecialchars((string) $data['email']) . '<br>' .
                '<br><b>Telefon:</b><br> ' . htmlspecialchars((string) $data['phone']) . '<br>' .
                '<br><b>Betreff der Anfrage:</b><br> ' . htmlspecialchars((string) $data['subject']) . '<br>' .
                '<br><b>Mitteilung:</b><br> ' . htmlspecialchars((string) $data['message']) . '<br><br><br>' .
                'Diese Email wurde automatisch generiert. Bitte antworten Sie nicht auf den Absender!';

            /** @var MailMessage $mail */
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail->setSubject($subject);
            $mail->setFrom(array($this->custom_settings['emailFrom'] => 'Kontaktformular'));
            $mail->setReplyTo(array($data['email']));
            $mail->setTo($receiver);
            //$mail->setBody('Here is the message itself');
            $mail->html($message);
            //$mail->addPart( $message, 'text/html' );
            $mail->send();
        }

        // write response
        return $this->jsonResponse((string) json_encode(
            [
                'errors' => $errors,
                //'post'            => $data,
                //'xurl'            => $xurl,
                //'receiver'        => $receiver,
                //'item_id'        => $item_id,
                //'x'               => uniqid(),
            ]
        ));
    }

    private function getRecaptchaResponse(mixed $form_response): string|bool
    {
        $requestURL = "https://api.friendlycaptcha.com/api/v1/siteverify";
        $re_secret = $this->custom_settings['reCaptchaCode'];

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
        $result = json_decode((string) file_get_contents($requestURL, false, $context), true);

        return $result['success'];
    }
}
