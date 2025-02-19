<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Evaluation;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * mk@rms 2022-05-12
 * see https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Eval.html
 */
class MultipleEmailEvaluation
{
    /**
     * check each email address in  a string with comma separated emails
     * return a string that contains only valid addresses separated by comma
     * if there was a invalid email it will be removed and a error message is displayed
     *
     * @param string $csv_value
     * @param bool $set
     * @param bool $show_flash_message_on_error
     * @return string
     */
    private function filterValidEmails(string $csv_value, bool &$set, bool $show_flash_message_on_error = true)
    {
        $has_errors = false;
        $array = explode(',', $csv_value);
        $valid_addresses = [];
        $trimmed_values = []; // contains addresses without spaces before or after
        $faulty_addresses = [];
        foreach ($array as $mail_address) {
            $mail_address = trim($mail_address);
            if (!filter_var($mail_address, FILTER_VALIDATE_EMAIL)) {
                $has_errors = true;
                $faulty_addresses[] = $mail_address;
            } else {
                $valid_addresses[] = $mail_address;
            }
        }

        if ($has_errors && $show_flash_message_on_error) {
            $this->flashMessage(
                'Fehlerhafte Empfängeradresse(n)',
                'Speichern nicht möglich: Fehlerhafte Empfängeradresse (n): ' .
                implode(' | ', $faulty_addresses),
                2, // AbstractMessage::ERROR // Verwenden Sie die `int`-Konstante aus AbstractMessage
            );
            $set = true; // do not save value
        }

        #return $value;
        return implode(',', $valid_addresses);
    }

    /**
     * JavaScript code for client side validation/evaluation
     *
     * @return string JavaScript code for client side validation/evaluation
     */
    public function returnFieldJS(): string
    {
        return 'return value;';
    }

    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $is_in The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not.
     * @return string Evaluated field value
     */
    public function evaluateFieldValue(string $value, string $is_in, bool &$set): string
    {
        return $this->filterValidEmails($value, $set, true);
    }

    /**
     * Server-side validation/evaluation on opening the record
     *
     * @param array $parameters Array with key 'value' containing the field value from the database
     * @return string Evaluated field value
     */
    public function deevaluateFieldValue(array $parameters): string
    {
        #return $parameters['value'];
        $set = true;

        return $this->filterValidEmails($parameters['value'], $set, false);
    }

    /**
     * @param string $messageTitle
     * @param string $messageText
     * @param int $severity see \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR
     */
    protected function flashMessage(string $messageTitle, string $messageText, int $severity = 2): void
    {
        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $messageText,
            $messageTitle,
            $severity,
            true
        );

        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);
    }
}
