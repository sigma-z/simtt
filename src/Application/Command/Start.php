<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Start extends Command
{

    protected static $defaultName = 'start';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Starts a timer');
        $this->addArgument('startTime', InputArgument::OPTIONAL, 'time format: hhmm or hmm');
        $this->addArgument('taskTitle', InputArgument::OPTIONAL, 'task title');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->getName());
        return 0;
    }

}
