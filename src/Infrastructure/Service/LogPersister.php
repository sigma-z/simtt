<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\Model\LogEntry;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogPersister
{

    /** @var string */
    private $logDir;

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
    }

    public function saveLog(LogEntry $logEntry): void
    {
        $entries = $this->getEntries();
        $found = false;
        $fh = $this->getFileHandle();
        foreach ($entries as $entry) {
            if ($entry->equals($logEntry)) {
                fwrite($fh, $logEntry . "\n");
                $logEntry->setId($this->getBaseId() . '-' . $logEntry->startTime);
                $found = true;
            }
            else {
                fwrite($fh, $entry . "\n");
            }
        }
        if (!$found) {
            fwrite($fh, $logEntry . "\n");
            $logEntry->setId($this->getBaseId() . '-' . $logEntry->startTime);
        }
        fclose($fh);
    }

    /**
     * @return LogEntry[]
     */
    private function getEntries(): array
    {
        $file = $this->getFile();
        $baseId = $this->getBaseId();
        if (!is_file($file)) {
            return [];
        }
        $lines = file($file);
        return array_filter(array_map(static function (string $line) use ($baseId) {
            if (trim($line)) {
                return LogEntry::fromString($line, $baseId);
            }
            return null;
        }, $lines));
    }

    private function getFileHandle()
    {
        $file = $this->getFile();
        $dir = dirname($file);
        $this->createDirectory($dir);
        return fopen($file, 'wb');
    }

    public function getFile(): string
    {
        $date = date('Y-m-d');
        [$year, $month, ] = explode('-', $date);
        return "$this->logDir/$year/$month/$date.log";
    }

    private function createDirectory(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    private function getBaseId(): string
    {
        return date('Y-m-d');
    }
}
