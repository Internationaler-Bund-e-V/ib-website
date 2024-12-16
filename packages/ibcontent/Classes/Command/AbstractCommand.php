<?php

/**
 * Abstract command.
 */

declare(strict_types=1);

namespace Rms\Ibcontent\Command;

use Symfony\Component\Console\Command\Command;

/**
 * AbstractCommand.
 */
abstract class AbstractCommand extends Command
{
    /**
     * Set generic prefix for the description.
     *
     * @param string $description
     *
     * @return Command
     */
    public function setDescription($description)
    {
        return parent::setDescription('IbContent task: ' . $description);
    }
}
