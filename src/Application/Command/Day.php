<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Command\Helper\LogAggregationTable;
use Simtt\Application\Command\Helper\LogTable;
use Simtt\Infrastructure\Service\LogHandler;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Day extends Command
{

    protected static $defaultName = 'day';

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

        $this->setDescription('Show logged time information for the given day');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from today');
        $this->addArgument('sum', InputArgument::OPTIONAL, 'flag to show the summarize');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputArguments($input);

        $logFileFinder = $this->logHandler->getLogFileFinder();
        $dateTime = $this->getDateTime($input->getArgument('offset'));
        $logFile = $logFileFinder->getLogFileForDate($dateTime);
        $entries = $logFile->getEntries();

        if (!$entries) {
            $output->writeln('No entries found for ' . $this->getFormattedDate($dateTime));
            return 0;
        }

       if ($input->getArgument('sum') === 'sum') {
            $logAggTable = new LogAggregationTable(new Table($output));
            $logAggTable->processLogEntries($entries);
            $logAggTable->render();
        }
        else {
            $logTable = new LogTable(new Table($output));
            $logTable->processLogEntries($entries);
            $logTable->render();
        }

        return 0;
    }

    private function getDateTime($offset): \DateTime
    {
        $dateTime = new \DateTime();
        if (is_numeric($offset)) {
            $dateTime->sub(new \DateInterval("P{$offset}D"));
        }
        return $dateTime;
    }

    private function processInputArguments(InputInterface $input): void
    {
        $offset = $input->getArgument('offset');
        if ($offset && !is_numeric($offset)) {
            $input->setArgument('offset', null);
            $input->setArgument('sum', $offset);
        }
    }

    private function getFormattedDate(\DateTime $dateTime): string
    {
        $today = new \DateTime();
        $formattedDate = $dateTime->format('Y-m-d');
        if ($formattedDate === $today->format('Y-m-d')) {
            return 'today';
        }
        if ($formattedDate === $today->sub(new \DateInterval('P1D'))->format('Y-m-d')) {
            return 'yesterday';
        }
        return $formattedDate;
    }

}
