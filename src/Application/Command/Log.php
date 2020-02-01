<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Command\Helper\ArraysRangeSelector;
use Simtt\Application\Command\Helper\LogFileEntriesFetcher;
use Simtt\Application\Command\Helper\LogTable;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogFile;
use Simtt\Infrastructure\Service\LogHandler;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Log extends Command
{

    protected static $defaultName = 'log';

    /** @var LogHandler */
    private $logHandler;

    /** @var int */
    private $showLogItemsDefault;

    /** @var int */
    private $start = 1;

    /** @var int */
    private $end = 0;

    /** @var Time|null */
    private $stopTimeByFollowingEntry;

    public function __construct(LogHandler $logHandler, int $showLogItemsDefault)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
        $this->showLogItemsDefault = $showLogItemsDefault;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Show logged time information');
        $this->addArgument('range-selection', InputArgument::OPTIONAL, 'selection or range of days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputArguments($input);

        $logFileFinder = $this->logHandler->getLogFileFinder();

        $logFiles = $logFileFinder->getLogFiles();
        $logEntries = $this->getLogEntries($logFiles);
        $numberOfEntries = count($logEntries);
        if ($numberOfEntries === 0) {
            $output->writeln('No entries found');
            return 0;
        }

        $logTableHelper = new LogTable(new Table($output), $this->stopTimeByFollowingEntry);
        $logTableHelper->processLogEntries($logEntries);
        $logTableHelper->render();

        return 0;
    }

    private function processInputArguments(InputInterface $input): void
    {
        $this->end = $this->showLogItemsDefault;
        $selectionRange = $input->getArgument('range-selection');
        if ($selectionRange) {
            $selectionRangeParts = explode('-', $selectionRange, 2);
            if (count($selectionRangeParts) === 2) {
                [$this->start, $this->end] = $selectionRangeParts;
            }
            else {
                $this->end = $selectionRange === 'all' ? 0 : $selectionRange;
            }
        }
    }

    /**
     * @param LogFile[] $logFiles
     * @return LogEntry[]
     */
    private function getLogEntries(array $logFiles): array
    {
        $rangeSelector = new ArraysRangeSelector((int)$this->start, (int)$this->end + 1);
        $elements = $rangeSelector->getElements(new LogFileEntriesFetcher($logFiles));

        // getting stop time for the last element by retrieving start time of the last element out of range
        if (count($elements) > $this->end - $this->start + 1) {
            $firstEntryOutOfRange = array_pop($elements);
            $this->stopTimeByFollowingEntry = $firstEntryOutOfRange->startTime;
        }
        return $elements;
    }

}
