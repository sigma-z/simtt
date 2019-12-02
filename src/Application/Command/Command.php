<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
abstract class Command extends SymfonyCommand
{

    protected function configure(): void
    {
        $file = __DIR__  . '/help/' . $this->getName() . '.md';
        $help = file_exists($file) ? file_get_contents($file) : $this->getName();
        $this->setHelp($help);

        parent::configure();
    }

}
