<?php

declare(strict_types=1);

namespace Ib\IbFormbuilder\Domain\Model;

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
class Form extends AbstractEntity
{
    protected string $name = '';
    protected string $formdataJson = '';
    protected string $receiver = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFormdataJson(): string
    {
        return $this->formdataJson;
    }

    public function setFormdataJson(string $formdataJson): void
    {
        $this->formdataJson = $formdataJson;
    }

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function setReceiver(string $receiver): void
    {
        $this->receiver = $receiver;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }
}
