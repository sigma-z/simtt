<?php
declare(strict_types=1);

namespace Simtt\Domain\Model;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntry
{

    private const SEPARATOR = ';';

    /** @var string|null */
    private $id;

    /** @var Time|null */
    public $startTime;

    /** @var Time|null */
    public $stopTime;

    /** @var string */
    public $task = '';

    /** @var string */
    public $comment = '';

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isPersisted(): bool
    {
        return !empty($this->id);
    }

    public function equals(LogEntry $entry): bool
    {
        return $this->getId() === $entry->getId();
    }

    public function __toString(): string
    {
        if (!$this->startTime) {
            return '';
        }
        $parts = [
            $this->startTime,
            ($this->stopTime ? (string)$this->stopTime : '     '),
            '"' . addslashes($this->task) . '"',
            '"' . addslashes($this->comment) . '"'
        ];
        return implode(self::SEPARATOR, $parts);
    }

    public static function fromString(string $raw, string $baseId = null): self
    {
        $parts = explode(self::SEPARATOR, $raw, 4);
        $log = new self();
        $log->startTime = self::parseTime($parts[0]);
        $log->stopTime = isset($parts[1]) ? self::parseTime($parts[1]) : null;
        $log->task = isset($parts[2]) ? self::parseText($parts[2]) : '';
        $log->comment = isset($parts[3]) ? self::parseText($parts[3]) : '';
        if ($baseId) {
            $log->setId($baseId . '-' . $log->startTime);
        }
        return $log;
    }

    private static function parseTime(string $time): ?Time
    {
        $time = trim($time);
        return $time ? new Time($time) : null;
    }

    private static function parseText(string $text): string
    {
        $text = trim($text);
        if ($text) {
            if (strpos($text, '"') === 0) {
                $text = substr($text, 1);
            }
            if (substr($text, -1) === '"') {
                $text = substr($text, 0, -1);
            }
            return stripslashes($text);
        }
        return '';
    }
}
