<?php

declare(strict_types=1);

namespace Ib\IbFormbuilder\Controller;

use Psr\Http\Message\ResponseInterface;
use Ib\IbFormbuilder\Domain\Model\Emaildata;
use Ib\IbFormbuilder\Domain\Model\Form;
use Ib\IbFormbuilder\Domain\Repository\ContentRepository;
use Ib\IbFormbuilder\Domain\Repository\EmaildataRepository;
use Ib\IbFormbuilder\Domain\Repository\FormRepository;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/***
 *
 * This file is part of the "IB Formbuilder" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Michael Kettel <mkettel@gmail.com>, rms. relationship marketing solutions GmbH
 *
 ***/

/**
 * FormController
 */
class FormController extends ActionController
{
    protected FormRepository $formRepository;
    protected EmaildataRepository $emaildataRepository;
    protected ContentRepository $contentRepository;

    //private readonly array $customSettings;
    private readonly array $customSettingsIbContent;

    protected ModuleTemplateFactory $moduleTemplateFactory;

    protected PageRenderer $pageRenderer;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    public function __construct(EmaildataRepository $emaildataRepository, FormRepository $formRepository, ContentRepository $contentRepository, ModuleTemplateFactory $moduleTemplateFactory, PageRenderer $pageRenderer)
    {
        $this->emaildataRepository = $emaildataRepository;
        $this->formRepository = $formRepository;
        $this->contentRepository = $contentRepository;

        /** @var ExtensionConfiguration $ext_conf  */
        $ext_conf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        //$this->customSettings = $ext_conf->get('ib_formbuilder');
        $this->customSettingsIbContent = $ext_conf->get('ibcontent');
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRenderer = $pageRenderer;
    }

    protected function initializeAction(): void
    {
        $version = time();

        // see typo3/packages/ib_formbuilder/Configuration/JavaScriptModules.php
        #$this->pageRenderer->addJsLibrary('jquery', 'EXT:ib_formbuilder/Resources/Public/libs/jQuery/jquery.min.js');
        #$this->pageRenderer->addJsLibrary('jquery_sortable', 'EXT:ib_formbuilder/Resources/Public/libsStatic/jquery-ui-sortable/jquery-ui-sortable.min.js');
        #$this->pageRenderer->addJsLibrary('form_builder', 'EXT:ib_formbuilder/Resources/Public/libs/formBuilder/form-builder.min.js');
        #$this->pageRenderer->addJsLibrary('form_renderer', 'EXT:ib_formbuilder/Resources/Public/libs/formBuilder/form-render.min.js');
        #$this->pageRenderer->addJsLibrary('form_backend', 'EXT:ib_formbuilder/Resources/Public/JavaScript/backend.min.js');
        #$this->pageRenderer->addJsLibrary('form_scripts', 'EXT:ib_formbuilder/Resources/Public/libsStatic/initBackendFormBuilderScripts.js');

        // only load backend modules (including jQuery) in the backend
        // to avoid errors in the frontend where jQuery is loaded elsewhere
        // mk@rms, 2024-11-21
        if (ApplicationType::fromRequest($this->request)->isBackend()) {
            $this->pageRenderer->loadJavaScriptModule('@rms/mfbb');
            $this->pageRenderer->loadJavaScriptModule('@rms/myinit');
        }
    }

    /**
     * validate a user-captcha (from form frontend) with googles service
     */
    /*
    private function getRecaptchaResponse(string $form_response): mixed
    {
        // setup recaptcha
        $requestURL = "https://www.google.com/recaptcha/api/siteverify";
        $re_secret = $this->customSettings['reCaptchaCode'];

        $postdata = http_build_query(
            [
                'secret' => $re_secret,
                'response' => $form_response
            ]
        );

        $opts = [
            'http' =>
            [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            ]
        ];

        $context = stream_context_create($opts);

        return json_decode((string)file_get_contents($requestURL, false, $context), true);
    }
    */

    /**
     * --------------------------------
     * FRONTEND
     * --------------------------------
     * show the form in the frontend
     */
    public function frontendShowFormAction(): ResponseInterface
    {
        $formId = $this->settings['form'];

        /** @var Form $formdata */
        $formdata = $this->formRepository->findByUid($formId);

        $json = html_entity_decode((string) $formdata->getFormdataJson());
        //$json = $formdata->getFormdataJson();
        $this->view->assign('formdata', $formdata);
        $this->view->assign('json', $json);
        //$this->view->assign('language', $GLOBALS['TSFE']->lang);
        $this->view->assign('formId', $formId);
        $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);

        return $this->htmlResponse();
    }

    /**
     * --------------------------------
     * FRONTEND AJAX SUBMIT
     * --------------------------------
     * this handles the ajax submit call
     */
    public function frontendFormAjaxSubmitAction(): ResponseInterface
    {
        // setup vars
        $emailContent = "";
        $receivers = [];
        $formDataDb = null;
        $success = false;
        $mail_send_success = false;
        $mail_has_valid_receivers = true;
        $flexformData = [];
        $receiversDatabase = [];
        $formDataDbArray = [];
        $errors = [];
        $formId = 0;
        $formDataDbName = '';

        // read the email receivers from the original plugins
        // flexform configuration
        $allArguments = $this->request->getArguments();

        if ((int) $allArguments['formdata']['hidden_uid'] >= 1 && $this->request->hasArgument('formdata')) {
            $ttcontentUid = (int) $allArguments['formdata']['hidden_uid'];

            // this holds the formdata, entered by the user
            $formData = $allArguments['formdata'];

            // loop through the formdata and create the final email text
            $emailContent .= '<ul>';
            foreach ($allArguments['formdata'] as $key => $value) {
                $is_not_hidden = strpos((string) $key, 'hidden_') === false;
                $is_not_captcha_data = strpos((string) $key, 'frc-captcha-solution') === false;
                if ($is_not_hidden && $is_not_captcha_data) {
                    $emailContent .= '<li><b>' . $key . '</b> : ' . $value . '</li>';
                }
            }
            $emailContent .= '</ul>';

            // ---------------------------------------------------------------
            // get the formbuilders json data (the structure of this form)
            // we use this to validate the user input
            // ---------------------------------------------------------------
            $formId = (int) $allArguments['formdata']['hidden_formId'];

            /** @var Form $formDataDb */
            $formDataDb = $this->formRepository->findByUid($formId);
            $formDataDbArray = json_decode((string) $formDataDb->getFormdataJson(), true);
            $formDataDbName = $formDataDb->getName();

            // ---------------------------------------------------------------
            // loop through the database form structure and
            // validate all user input against this data
            // ---------------------------------------------------------------
            foreach ($formDataDbArray as $key => $value) {
                // read the relevant properties for the actual element
                $name = $value['name'];
                $required = $value['required'];
                $type = $value['type'];
                $subType = $value['subtype'];

                // get the value from the submitted form for the actual element
                $submittedValue = $formData[$name];

                if ($subType === 'email') {
                    if (!filter_var($submittedValue, FILTER_VALIDATE_EMAIL)) {
                        $errors[$name] = "no_valid_email";
                    }
                }

                // check inputs that are required
                if ($required) {
                    // check if there is a value that matches
                    // the checkbox name from the database
                    // if not, create an error
                    if ($type === 'checkbox-group') {
                        $keys = array_keys($formData);
                        $res = preg_grep("/^(" . $name . ")(.*)/", $keys);
                        if (empty($res)) {
                            // make the label of the first checkbox red
                            $errors[$name . '-0'] = "empty";
                        }
                    } else {
                        // for all other input types, check if there is a submitted value
                        if (!isset($submittedValue) || $submittedValue === "") {
                            $errors[$name] = "empty";
                        }
                    }
                }
            }

            // ---------------------------------------------------------------
            // read the receiver list from the database
            // then check if there are receivers in the flexform
            // if there are receivers in the flexform, they will be used
            // instead of the database receivers
            // ---------------------------------------------------------------
            /** @var Form $mytmpform */
            $mytmpform = $this->formRepository->findByUid($formId);
            $receiversDatabase = $mytmpform->getReceiver();
            $receiversDatabase = array_map('trim', explode(',', (string) $receiversDatabase));
            $receivers = $receiversDatabase;

            $flexformData = $this->contentRepository->getFlexformForContentUid($ttcontentUid);

            if ($flexformData[0] !== '') {
                $receivers = $flexformData['receivers'];
            }

            $final_valid_receivers = [];
            foreach ($receivers as $receiver) {
                if (!filter_var($receiver, FILTER_VALIDATE_EMAIL)) {
                    $mail_has_valid_receivers = false;
                } else {
                    $final_valid_receivers[] = trim((string) $receiver);
                }
            }

            // ---------------------------------------------------------------
            // if there are no errors, check the captcha from the frontend
            // if this contains a valid response, submit the email
            // ---------------------------------------------------------------
            if (empty($errors)) {
                //$mailRecaptcha = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('g-recaptcha-response');
                #$mailRecaptcha = $allArguments['formdata']['g-recaptcha-response'];
                #$captchaResponse = $this->getRecaptchaResponse($mailRecaptcha);
                #$captchaSuccess = $captchaResponse['success'];
                #if (!$captchaSuccess) {
                #    $errors['captcha'] = true;
                #}

                $requestURL = "https://api.friendlycaptcha.com/api/v1/siteverify";
                $re_secret = $this->customSettingsIbContent['reCaptchaCode'];

                $postdata = http_build_query(
                    array(
                        'solution' => $allArguments['formdata']['frc-captcha-solution'],
                        'secret' => $re_secret,
                    ),
                );

                $opts = array(
                    'http' =>
                    array(
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $postdata,
                    ),
                );

                $result = [];
                if ($allArguments['formdata']['frc-captcha-solution'] === null) {
                    //return $result['error'];
                    //return 'error';
                    return $this->htmlResponse('error');
                }

                $context = stream_context_create($opts);
                $result = json_decode((string) file_get_contents($requestURL, false, $context), true);

                if ($result['success'] === false) {
                    $errors['captcha'] = true;
                }
            }
        }

        // ---------------------------------------------------------------
        // test mode. This outputs more info in the json return
        // and does not send the email
        // ---------------------------------------------------------------
        //$testmode = false;
        /*
        if ($testmode) {
            $errors['x'] = 'y';
            $this->emaildataRepository->saveFormData(
                $emailContent,
                $flexformData['saveToDatabaseName'],
                $formDataDb->getPid(),
                $formId,
                $errorOnSend = true
            );
        }
        */
        // ------------------------------------------
        // if there are no errors, send the mail
        // and save data into the db if configured
        // ------------------------------------------
        if (empty($errors)) {
            $success = true;

            if (!empty($final_valid_receivers)) {

                /** @var MailMessage $mail */
                $mail = GeneralUtility::makeInstance(MailMessage::class);
                $name = $flexformData['saveToDatabaseName'];
                if ($name === '') {
                    $name = $formDataDbName;
                }

                // --------------------------
                // Prepare and send the email
                // --------------------------
                $mail_send_success = $mail
                    ->setSubject((string) $name)
                    ->setFrom(array('noreply@internationaler-bund.de' => 'noreply@internationaler-bund.de'))
                    ->setTo($final_valid_receivers)
                    ->html($emailContent)
                    //->addPart($emailContent, 'text/html')
                    ->send();
            }

            if (!$mail_send_success || !$mail_has_valid_receivers) {
                $mail_send_success = false;
            }

            // --------------------------
            // save data into database
            // we always do this, even if sending of mails does not work
            // --------------------------
            if ($flexformData['saveToDatabase'] || !$mail_send_success) {
                $name = $flexformData['saveToDatabaseName'];
                if ($name === '') {
                    $name = $formDataDbName;
                }

                $this->emaildataRepository->saveFormData(
                    $emailContent,
                    $name,
                    $formDataDb->getPid(),
                    $formId,
                    !$mail_send_success
                );
            }
        }

        // ------------------------------------------
        // create and return json output
        // ------------------------------------------
        $toReturn = [
            'success' => $success,
            'mail_send_success' => $mail_send_success,
            'errors' => $errors,
            'all_emails_valid' => $mail_has_valid_receivers,
            //'captchaResponse' => $captchaResponse,
        ];

        /*
        if ($testmode) {
            $toReturn['formDataDbArray'] = $formDataDbArray;
            $toReturn['emailContent'] = $emailContent;
            $toReturn['receiversDatabase'] = $receiversDatabase;
            $toReturn['flexformData'] = $flexformData;
            $toReturn['receivers'] = $receivers;
            $toReturn['arguments'] = $allArguments;
        }
        */

        //return json_encode($toReturn);
        return $this->jsonResponse((string) json_encode($toReturn));
    }

    /**
     * action list
     */
    public function listAction(): ResponseInterface
    {
        // get current storagePid from typoscript settings
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $storagePid = $extbaseFrameworkConfiguration['plugin.']['tx_ibformbuilder_showform.']['persistence.']['storagePid'];

        // find all emails
        $this->emaildataRepository->setDefaultOrderings(array('crdate' => QueryInterface::ORDER_DESCENDING));
        $emails = $this->emaildataRepository->findAll();

        $forms = $this->formRepository->findAll();

        $values = [
            'forms' => $forms,
            'emails' => $emails,
            'storagePid' => $storagePid,
        ];

        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibformbuilder')->assignMultiple($values)->renderResponse('Backend/List');
    }

    /**
     * action show
     */
    public function showAction(Form $form): ResponseInterface
    {
        $this->view->assign('form', $form);

        return $this->htmlResponse();
    }

    /**
     * action new
     */
    public function newAction(): ResponseInterface
    {
        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibformbuilder')->renderResponse('Backend/New');
    }

    /**
     * action create
     */
    public function createAction(Form $newForm): ResponseInterface
    {
        //$this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html',
        //    '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->formRepository->add($newForm);

        return $this->redirect('list');
    }

    /**
     * action edit
     */
    #[IgnoreValidation(['argumentName' => 'form'])]
    public function editAction(Form $form): ResponseInterface
    {

        $values = [
            'form' => $form,
        ];

        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibformbuilder')->assignMultiple($values)->renderResponse('Backend/Edit');
    }

    /**
     * action update
     */
    public function updateAction(Form $form): ResponseInterface
    {
        //$this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html',
        //    '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->formRepository->update($form);
        //$this->redirect('edit');
        //$this->forward('new',NULL,NULL,array('form' => $form));

        // ------------------------------------------------------------------
        // delete any temporary vhs-instances of this form in typo3temp/assets
        // otherwise they won't update with the updated form structure
        // @see https://mantis.rm-solutions.de/mantis/view.php?id=1268
        // ------------------------------------------------------------------
        $form_uid = $form->getUid();
        $path_temp = Environment::getPublicPath() . '/typo3temp/assets/';
        $handle = opendir($path_temp);
        if ($handle) {
            while (($entry = readdir($handle)) !== false) {
                if (strpos($entry, 'vhs-assets-form_' . $form_uid) !== false) {
                    if (file_exists($path_temp . $entry)) {
                        unlink($path_temp . $entry) or die("Couldn't delete file");
                    }
                }
            }
            closedir($handle);
        }

        return $this->redirect('edit', 'Form', null, array('form' => $form));
    }

    /**
     * action delete
     */
    public function deleteAction(Form $form): ResponseInterface
    {
        //$this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html',
        //    '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->formRepository->remove($form);

        return $this->redirect('list');
    }

    /**
     * action showEmailData
     */
    public function showEmailDataAction(Emaildata $emaildata): ResponseInterface
    {

        $values = [
            'emaildata' => $emaildata,
        ];

        return $this->moduleTemplateFactory->create($this->request)->setModuleClass('tx-ibformbuilder')->assignMultiple($values)->renderResponse('Backend/ShowEmailData');
    }

    /**
     * action delete emailData
     */
    public function deleteEmailDataAction(Emaildata $emaildata): ResponseInterface
    {
        $this->emaildataRepository->remove($emaildata);

        return $this->redirect('list');
    }

    /**
     * action export emailData
     */
    public function exportAction(Form $form): ResponseInterface
    {
        $this->exportEmailDataAsCSVAction($form->getUid());

        return $this->htmlResponse();
    }

    /**
     * this function creates a csv export with all emaildata inside the current sysfolder
     * and downloads a csv
     */
    private function exportEmailDataAsCSVAction(int $formID): void
    {
        $this->emaildataRepository->setDefaultOrderings(
            array('crdate' => QueryInterface::ORDER_DESCENDING)
        );
        $emails = $this->emaildataRepository->findByRelatedFormId($formID);

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="emaildata.csv";');

        $forms = array();
        $headerRow = array('datum' => 0);
        $fp = fopen('php://output', 'w');

        if ($fp) {

            /** @var Emaildata $email */
            foreach ($emails as $email) {
                $error_on_send = $email->getErrorOnSend();
                $content = $email->getEmaildataHtml();
                $content = str_replace('</li>', "---nl---</li>", (string) $content);
                $content = str_replace(' : ', ": ", $content);
                $content = strip_tags($content);

                /**
                 * generate header row(s) and prepare row data
                 */
                $record = explode("---nl---", $content);
                $record[] = 'error_on_send: ' . (int) $error_on_send;
                $attributes = array('datum' => date('d.m.Y - H:i', $email->getTstamp()));

                foreach ($record as $rec) {
                    if ($rec != '') {
                        $singleAttribute = explode(": ", $rec);
                        //use only first element for header, then remove and merge others in case of multiple ":" -> see MA#1577
                        $tempHeader = $singleAttribute[0];
                        unset($singleAttribute[0]);
                        $tmpValue = implode(": ", $singleAttribute);
                        $headerRow[$tempHeader] = $tempHeader;
                        $attributes[$tempHeader] = $tmpValue;
                    }
                }
                $forms[] = $attributes;
            }
            fputcsv($fp, $headerRow);

            foreach ($forms as $formRow) {
                $tmpCsvRow = array();
                foreach ($headerRow as $hrKey => $value) {
                    if (!isset($formRow[$hrKey])) {
                        $tmpCsvRow[$hrKey] = 'n/a';
                    } else {
                        $tmpCsvRow[$hrKey] = $formRow[$hrKey];
                    }
                }
                fputcsv($fp, $tmpCsvRow);
            }
            fclose($fp);
        }
        exit;
    }
}
