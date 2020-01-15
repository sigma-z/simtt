<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\Model\LogEntry;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogHandler
{

    /** @var LogFileFinder */
    private $logFileFinder;

    public function __construct(LogFileFinder $logFileFinder)
    {
        $this->logFileFinder = $logFileFinder;
    }

    public function getLogFileFinder(): LogFileFinder
    {
        return $this->logFileFinder;
    }

    /**
     * @return LogEntry[]
     */
    public function getAllLogs(): array
    {
        $allEntries = [];
        foreach ($this->logFileFinder->getLogFiles() as $logFile) {
            $entries = $logFile->getEntries();
            $allEntries = array_merge($allEntries, $entries);
        }
        return $allEntries;
    }

    public function getLastLog(): ?LogEntry
    {
        $entries = $this->getLogEntries();
        return end($entries) ?: null;
    }

    public function getLogReverseIndex(int $reverseIndex)
    {
        $entries = $this->getLogEntries();
        $index = (int)(count($entries) - (abs($reverseIndex) + 1));
        return $entries[$index] ?? null;
    }

    /**
     * @return LogEntry[]
     */
    private function getLogEntries(): array
    {
        $logFile = LogFile::createTodayLogFile($this->logFileFinder->getPath());
        return $logFile->getEntries();
    }

}
