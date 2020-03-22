<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Yesterday extends Command
{

    protected static $defaultName = 'yesterday';


    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Shows logged time information for yesterday');
        $this->addArgument('sum', InputArgument::OPTIONAL, 'flag to show the summarize');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @noinspection NullPointerExceptionInspection */
        $command = $this->getApplication()->find('day');
        $stringInput = 'day 1' . ($input->getArgument('sum') === 'sum' ? ' sum' : '');
        $commandInput = new StringInput($stringInput);
        $command->run($commandInput, $output);
        return 0;
    }
}
