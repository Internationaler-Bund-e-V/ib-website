<?php

/**
 * Abstract command.
 */

declare(strict_types=1);

namespace Rms\Ibsearch\Command;

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
        return parent::setDescription('IbSearch task: ' . $description);
    }
}
