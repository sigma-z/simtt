<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Application\Prompter\PrompterInterface;
use Simtt\Application\Task\TaskPrompterInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class TaskPrompter implements TaskPrompterInterface
{

    /**
     * @param string[] $tasks
     * @param PrompterInterface $prompter
     */
    public function promptTask(array $tasks, PrompterInterface $prompter): string
    {
        $taskName = $prompter->prompt('task> ');
        if (strpos($taskName, '#') === 0) {
            /** @var int|string $index */
            $index = substr($taskName, 1);
            if (isset($tasks[$index - 1])) {
                return $tasks[$index - 1]->getTask();
            }
        }
        return $taskName;
    }
}
