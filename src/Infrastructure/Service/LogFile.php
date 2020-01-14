<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\Model\LogEntry;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogFile
{

    public const FILE_EXT = '.csv';

    /** @var string */
    private $file;

    /** @var string */
    private $date;

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->date = pathinfo($file, PATHINFO_FILENAME);
    }

    public static function createTodayLogFile(string $logDir): self
    {
        $date = (new \DateTime())->format('Y-m-d');
        [$year, $month, ] = explode('-', $date);
        $file = "$logDir/$year/$month/$date" . self::FILE_EXT;
        return new LogFile($file);
    }

    public function saveLog(LogEntry $logEntry): void
    {
        $entries = $this->getEntries();
        $found = false;
        $fh = $this->openFileWriteHandle();
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
    public function getEntries(): array
    {
        if (!$this->isPersisted()) {
            return [];
        }
        $baseId = $this->getBaseId();
        $lines = file($this->getFile());
        return array_filter(array_map(static function (string $line) use ($baseId) {
            if (trim($line)) {
                return LogEntry::fromString($line, $baseId);
            }
            return null;
        }, $lines));
    }

    private function openFileWriteHandle()
    {
        $file = $this->getFile();
        $dir = dirname($file);
        $this->createDirectory($dir);
        return fopen($file, 'wb');
    }

    private function createDirectory(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getBaseId(): string
    {
        return $this->date;
    }

    private function isPersisted(): bool
    {
        return is_file($this->getFile());
    }
}
