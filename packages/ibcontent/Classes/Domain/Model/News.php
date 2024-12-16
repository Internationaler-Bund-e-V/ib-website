<?php

declare(strict_types=1);

namespace Rms\Ibcontent\Domain\Model;

/**
 * News
 */
class News extends \GeorgRinger\News\Domain\Model\News
{
    /**
     * @var string
     */
    protected $subheadline;

    /**
     * @return string
     */
    public function getSubheadline()
    {
        return $this->subheadline;
    }

    /**
     * @param string $subheadline
     */
    public function setSubheadline($subheadline): void
    {
        $this->subheadline = $subheadline;
    }
}
