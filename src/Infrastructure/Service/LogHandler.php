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

        return null;
    }

    public function getCurrentLog(): ?LogEntry
    {
        return null;
    }

}
