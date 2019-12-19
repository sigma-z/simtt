<?php
declare(strict_types=1);

namespace Simtt\Domain\Model;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntry
{

    /** @var Time|null */
    public $startTime;

    /** @var Time|null */
    public $stopTime;

    /** @var string */
    public $task = '';

    /** @var string */
    public $comment = '';

    /** @var bool */
    public $persisted = false;


    public function __toString(): string
    {
        if (!$this->startTime) {
            return '';
        }
        $parts = [
            $this->startTime,
            ($this->stopTime ? (string)$this->stopTime : '     '),
            "\"$this->task\"",
            "\"$this->comment\""
        ];
        return implode(';', $parts);
    }
}
