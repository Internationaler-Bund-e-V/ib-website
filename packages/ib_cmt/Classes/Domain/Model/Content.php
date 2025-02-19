<?php

declare(strict_types=1);

namespace IB\IbCmt\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Content extends AbstractEntity
{
  /**
   * contentid
   *
   * @var int
   */
  protected $contentid;

  /**
   * contentparentid
   *
   * @var int
   */
  protected $contentparentid;

  /**
   * contenttype
   *
   * @var int
   */
  protected $contenttype = 0;

  /**
   * rtcontenttype
   *
   * @var int
   */
  protected $rtcontenttype;

  /**
   * allowed
   *
   * @var bool
   */
  protected $allowed = false;

  /**
   * comment
   *
   * @var string
   */
  protected $comment = '';

  /**
   * contenttstamp
   *
   * @var int
   */
  protected $contenttstamp = 0;

  /**
   * tstampallowed
   *
   * @var int
   */
  protected $tstampallowed = 0;

  public function setContentid(int $contentid): void
  {
    $this->contentid = $contentid;
  }

  public function getContentid(): int
  {
    return $this->contentid;
  }

  public function setContentparentid(int $contentparentid): void
  {
    $this->contentparentid = $contentparentid;
  }

  public function getContentparentid(): int
  {
    return $this->contentparentid;
  }

  public function setRtcontenttype(int $rtcontenttype): void
  {
    $this->rtcontenttype = $rtcontenttype;
  }

  public function getRtcontenttype(): int
  {
    return intval($this->rtcontenttype);
  }

  public function setContenttype(int $contenttype): void
  {
    $this->contenttype = $contenttype;
  }

  public function getContenttype(): int
  {
    return $this->contenttype;
  }

  public function setAllowed(bool $allowed): void
  {
    $this->allowed = $allowed;
  }

  public function getAllowed(): bool
  {
    return $this->allowed;
  }

  public function setComment(string $comment): void
  {
    $this->comment = $comment;
  }

  public function getComment(): string
  {
    return $this->comment;
  }

  public function setTstampallowed(int $tstampallowed): void
  {
    $this->tstampallowed = $tstampallowed;
  }

  public function getTstampallowed(): int
  {
    return $this->tstampallowed;
  }

  public function setContenttstamp(int $contenttstamp): void
  {
    $this->contenttstamp = $contenttstamp;
  }

  public function getContenttstamp(): int
  {
    return $this->contenttstamp;
  }
}
