<?php
declare(strict_types=1);

namespace Simtt\Domain\Model;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class RecentTask
{

    /** @var string */
    private $task;

    /** @var int */
    private $count = 1;

    public function __construct(string $task)
    {
        $this->task = $task;
    }

    public function increment(): void
    {
        $this->count++;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
