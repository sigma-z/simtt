<?php
declare(strict_types=1);

namespace Simtt\Domain\Model;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Time
{

    /** @var int */
    private $hour;

    /** @var int */
    private $minute;


    /**
     * @param string $time
     */
    public function __construct(string $time)
    {
        preg_match('/^(\d{1,2}):?(\d{2})$/', $time, $matches);
        if (count($matches) !== 3) {
            throw new \RuntimeException("'$time' is not recognized as a time");
        }
        $hour = (int)$matches[1];
        $minute = (int)$matches[2];
        if ($hour > 23 || $minute > 59) {
            throw new \RuntimeException("'$time' is not recognized as a time");
        }
        $this->hour = $hour;
        $this->minute = $minute;
    }

    /**
     * @return int
     */
    public function getHour(): int
    {
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getMinute(): int
    {
        return $this->minute;
    }

    public static function now(): self
    {
        $dateTime = new \DateTime('now');
        $time = $dateTime->format('Hi');
        return new self($time);
    }

    public function compare(Time $time): int
    {
        if ($this->getHour() < $time->getHour()) {
            return -1;
        }
        if ($this->getHour() === $time->getHour()) {
            if ($this->getMinute() < $time->getMinute()) {
                return -1;
            }
            if ($this->getMinute() === $time->getMinute()) {
                return 0;
            }
        }
        return 1;
    }

    public function isOlderThan(Time $time): bool
    {
        return $this->compare($time) === 1;
    }

    public function isNewerThan(Time $time): bool
    {
        return $this->compare($time) === -1;
    }

    public function diff(Time $time): \DateInterval
    {
        return (new \DateTime((string)$this))->diff(new \DateTime((string)($time)));
    }

    public function __toString(): string
    {
        return sprintf('%02d', $this->getHour()) . ':' . sprintf('%02d', $this->getMinute());
    }
}
