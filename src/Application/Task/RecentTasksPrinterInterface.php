<?php
declare(strict_types=1);

namespace Simtt\Application\Task;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
interface RecentTasksPrinterInterface
{
    /**
     * @param OutputInterface $output
     * @return string[]
     */
    public function outputTasks(OutputInterface $output): array;
}
