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

    /** @var Time */
    public $startTime;

    /** @var Time|null */
    public $stopTime;

    /** @var string */
    public $task = '';

    /** @var string */
    public $comment = '';

    public function __construct(Time $startTime, string $task = '', string $comment = '')
    {
        $this->startTime = $startTime;
        $this->task = $task;
        $this->comment = $comment;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDate(): ?string
    {
        return $this->id ? substr($this->id, 0, 10) : null;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

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
        $startTime = self::parseTime($parts[0]);
        $log = new self($startTime);
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

    public function diff(): string
    {
        if ($this->stopTime === null) {
            return '';
        }
        $dateInterval = $this->startTime->diff($this->stopTime);
        return sprintf('%d:%02d', $dateInterval->h, $dateInterval->i);
    }

    public static function getTimeDuration(Time $timeStart, ?Time $timeStop): string
    {
        $minutes = self::getTimeDurationInMinutes($timeStart, $timeStop);
        return self::formatDuration($minutes);
    }

    public static function getTimeDurationInMinutes(Time $timeStart, ?Time $timeStop): ?int
    {
        if (!$timeStop) {
            return null;
        }
        $hours = $timeStop->getHour() - $timeStart->getHour();
        $minutes = $timeStop->getMinute() - $timeStart->getMinute();
        if ($minutes < 0) {
            $hours--;
            $minutes = 60 + $minutes;
        }
        return $hours * 60 + $minutes;
    }

    public function getDuration(?Time $alternativeStopTime): string
    {
        $minutes = $this->getDurationInMinutes($alternativeStopTime);
        return self::formatDuration($minutes);
    }

    public function getDurationInMinutes(?Time $alternativeStopTime): ?int
    {
        $stopTime = $this->stopTime ?: $alternativeStopTime;
        if (!$stopTime) {
            return null;
        }
        $hours = $stopTime->getHour() - $this->startTime->getHour();
        $minutes = $stopTime->getMinute() - $this->startTime->getMinute();
        if ($minutes < 0) {
            $hours--;
            $minutes = 60 + $minutes;
        }
        return $hours * 60 + $minutes;
    }

    private static function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return '';
        }
        $hours = floor($minutes / 60);
        $minutes -= ($hours * 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
