<?php

declare(strict_types=1);

namespace Rms\IbContactPerson\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * ContactPerson
 */
class ContactPerson extends AbstractEntity
{
    /**
     * name
     *
     * @var string
     *
     */
    protected $name = '';

    /**
     * position
     *
     * @var string
     */
    protected $position = '';

    /**
     * contactInfo
     *
     * @var string
     */
    protected $contactInfo = '';

    /**
     * image
     *
     * @var FileReference
     */
    protected $image = null;

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the position
     *
     * @return string $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the position
     *
     * @param string $position
     * @return void
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Returns the contactInfo
     *
     * @return string $contactInfo
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * Sets the contactInfo
     *
     * @param string $contactInfo
     * @return void
     */
    public function setContactInfo($contactInfo)
    {
        $this->contactInfo = $contactInfo;
    }

    /**
     * Returns the image
     *
     * @return FileReference $image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets the image
     *
     * @param FileReference $image
     * @return void
     */
    public function setImage(FileReference $image)
    {
        $this->image = $image;
    }
}
