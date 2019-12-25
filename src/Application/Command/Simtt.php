<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Infrastructure\Prompter\Prompter;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Simtt extends Command
{

    protected static $defaultName = 'simtt';

    /** @var Prompter */
    private $prompter;

    /** @var Parser */
    private $parser;

    public function __construct(Parser $parser, Prompter $prompter)
    {
        parent::__construct();

        $this->parser = $parser;
        $this->prompter = $prompter;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Simtt (only) in interactive mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->prompter->setOutput($output);
        do {
            $result = $this->promptCommand();
            $continue = $this->runInteractiveCommand($result, $output);
        }
        while ($continue);
        return 0;
    }

    protected function promptCommand(): ParseResult
    {
        do {
            $command = $this->prompter->prompt('> ');
        }
        while (($result = $this->parser->parse($command)) === false);
        return $result;
    }

    protected function getCommand(ParseResult $result): SymfonyCommand
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getApplication()->find($result->getCommandName());
    }

    protected function runInteractiveCommand(ParseResult $result, OutputInterface $output): bool
    {
        if ($result->isExitCommand()) {
            $output->writeln('Ok, bye bye.');
            return false;
        }
        $input = $result->createInput();
        $command = $this->getCommand($result);
        $command->run($input, $output);
        return true;
    }

}
