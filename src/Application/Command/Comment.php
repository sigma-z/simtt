<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Infrastructure\Service\LogHandler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Comment extends Command
{

    protected static $defaultName = 'comment';

    /** @var LogHandler */
    private $logHandler;

    public function __construct(LogHandler $logHandler)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Sets comment for current log entry by offset, if given');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from last log entry of today', 0);
        $this->addArgument('comment', InputArgument::OPTIONAL, 'comment');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputArguments($input);

        $logFileFinder = $this->logHandler->getLogFileFinder();
        $dateTime = new \DateTime();
        $logFile = $logFileFinder->getLogFileForDate($dateTime);
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

        $comment = $input->getArgument('comment');
        /** @var int $index */
        $index = $numberOfEntries - $offset - 1;
        $logEntry = $entries[$index];
        $logEntry->comment = $comment;
        $logFile->saveLog($logEntry);

        $message = "Comment '$comment' updated for log started at {$logEntry->startTime}";
        if ($logEntry->task) {
            $message .= " for '{$logEntry->task}'";
        }
        $output->writeln($message);

        return 0;
    }

    private function processInputArguments(InputInterface $input): void
    {
        $offset = $input->getArgument('offset');
        if ($offset && !is_numeric($offset) && !$input->getArgument('comment')) {
            $input->setArgument('offset', 0);
            $input->setArgument('comment', $offset);
        }
    }
}
