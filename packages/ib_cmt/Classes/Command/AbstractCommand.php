<?php

declare(strict_types=1);

namespace IB\IbCmt\Command;

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
     * @return static
     */
    public function setDescription(string $description): static
    {
        return parent::setDescription('IbCmt task: ' . $description);
    }
}
