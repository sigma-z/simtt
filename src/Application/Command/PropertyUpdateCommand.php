<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\LogEntry;
use Simtt\Infrastructure\Prompter\Prompter;
use Simtt\Infrastructure\Service\LogFile;
use Simtt\Infrastructure\Service\LogHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
abstract class PropertyUpdateCommand extends Command
{

    /** @var LogHandler */
    protected $logHandler;

    /** @var Prompter */
    protected $prompter;

    abstract protected function processInputArguments(InputInterface $input): void;

    abstract protected function getMessageForActionPerformed(LogEntry $logEntry): string;

    public function __construct(LogHandler $logHandler, Prompter $prompter)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
        $this->prompter = $prompter;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputArguments($input);
        $logFile = $this->getLogFile();
        $entries = $logFile->getEntries();

        if (!$entries) {
            $output->writeln('No entries found for today');
            return 0;
        }

        $numberOfEntries = count($entries);
        $offset = $input->getArgument('offset');
        if ($offset >= $numberOfEntries) {
            $output->writeln('Offset ' . $offset . ' is out of range for today');
            return 0;
        }

        /** @var int $index */
        $index = $numberOfEntries - $offset - 1;
        $logEntry = $entries[$index];
        $this->updateLogEntry($input, $logEntry);
        $logFile->saveLog($logEntry);
        $message = $this->getMessageForActionPerformed($logEntry);
        $output->writeln($message);

        return 0;
    }

    private function getLogFile(): LogFile
    {
        $logFileFinder = $this->logHandler->getLogFileFinder();
        $dateTime = new \DateTime();
        return $logFileFinder->getLogFileForDate($dateTime);
    }

    protected function updateLogEntry(InputInterface $input, LogEntry $logEntry): void
    {
        $commandName = static::$defaultName;
        $property = $input->getArgument($commandName);
        if ($property === null) {
            $property = $this->prompter->prompt("$commandName> ");
        }
        $logEntry->{$commandName} = $property;
    }
}
