<?php
declare(strict_types=1);

namespace Simtt\Application\Task;

use Simtt\Domain\Model\RecentTask;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
interface RecentTaskListInterface
{
    /**
     * @param int $numOfTasks
     * @return RecentTask[]
     */
    public function getTasks(int $numOfTasks): array;
}
