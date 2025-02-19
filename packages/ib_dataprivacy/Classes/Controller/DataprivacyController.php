<?php

declare(strict_types=1);

namespace Ib\IbDataprivacy\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DataprivacyController extends ActionController
{
    public function listAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }
}
