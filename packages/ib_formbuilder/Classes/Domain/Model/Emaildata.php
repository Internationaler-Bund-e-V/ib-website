<?php

declare(strict_types=1);

namespace Rms\IbFormbuilder\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
 * Formulars
 */
class Emaildata extends AbstractEntity
{
    protected string $formName = '';
    protected string $emaildataHtml = '';
    protected string $emaildataCsv = '';
    protected int $relatedFormId = 0;
    protected int $errorOnSend = 0;
    protected int $tstamp;

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    public function setName(string $formName): void
    {
        $this->formName = $formName;
    }

    public function getEmaildataHtml(): string
    {
        return $this->emaildataHtml;
    }

    public function setEmaildataHtml(string $emailDataHtml): void
    {
        $this->emaildataHtml = $emailDataHtml;
    }

    public function getEmaildataCsv(): string
    {
        return $this->emaildataCsv;
    }

    public function setEmaildataCsv(string $emailDataCsv): void
    {
        $this->emaildataCsv = $emailDataCsv;
    }

    public function getRelatedFormId(): int
    {
        return $this->relatedFormId;
    }

    public function setRelatedFormId(int $relatedFormId): void
    {
        $this->relatedFormId = $relatedFormId;
    }

    public function getErrorOnSend(): int
    {
        return $this->errorOnSend;
    }

    public function setErrorOnSend(int $errorOnSend): void
    {
        $this->errorOnSend = $errorOnSend;
    }
}
