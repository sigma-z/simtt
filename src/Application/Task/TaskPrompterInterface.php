<?php
declare(strict_types=1);

namespace Simtt\Application\Task;

use Simtt\Application\Prompter\PrompterInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
interface TaskPrompterInterface
{
    /**
     * @param string[] $tasks
     * @param PrompterInterface $prompter
     */
    public function promptTask(array $tasks, PrompterInterface $prompter): string;
}
