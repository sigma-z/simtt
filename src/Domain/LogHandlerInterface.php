<?php
declare(strict_types=1);

namespace Simtt\Domain;

use Simtt\Domain\Model\LogEntry;

interface LogHandlerInterface
{

    public function getLogFileFinder(): LogFileFinderInterface;

    /**
     * @return LogEntry[]
     */
    public function getAllLogs(): array;

    public function getLastLog(): ?LogEntry;

    public function getLogReverseIndex(int $reverseIndex): ?LogEntry;
}
