<?php
declare(strict_types=1);

namespace Simtt\Domain\Model;

use Simtt\Infrastructure\Service\Clock\Clock;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Time
{

    /** @var string */
    public static $now = 'now';

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

    public static function now(Clock $clock): self
    {
        return new self($clock->getTime());
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
        return $this->compare($time) === -1;
    }

    public function isNewerThan(Time $time): bool
    {
        return $this->compare($time) === 1;
    }

    public function diff(Time $time): \DateInterval
    {
        return (new \DateTime((string)$this))->diff(new \DateTime((string)($time)));
    }

    public function roundBy(int $precision): void
    {
        if ($precision < 1) {
            $precision = 1;
        }
        $modulo = $this->minute % $precision;
        if ($modulo >= $precision / 2) {
            $this->minute = $this->minute - $modulo + $precision;
            if ($this->minute >= 60) {
                $this->minute = 0;
                if ($this->hour + 1 > 23) {
                    throw new \RuntimeException('Invalid hour, is bigger than 23!');
                }
                $this->hour++;
            }
        }
        elseif ($modulo > 0) {
            $this->minute -= $modulo;
        }
    }

    public function __toString(): string
    {
        return sprintf('%02d', $this->getHour()) . ':' . sprintf('%02d', $this->getMinute());
    }
}
