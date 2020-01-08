<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\LogEntry;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Stop extends TimerCommand
{

    protected static $defaultName = 'stop';

    protected function configure(): void
    {
        parent::configure();

        $this->setAliases(['stop*']);
        $this->setDescription('Stops a timer');
    }

    protected function getMessageForActionPerformed(LogEntry $logEntry, InputInterface $input): string
    {
        $time = self::$defaultName === 'start' ? $logEntry->startTime : $logEntry->stopTime;
        $message = $this->isUpdate($input)
            ? 'Timer stop updated to ' . $time
            : 'Timer stopped at ' . $time;
        if ($logEntry->task) {
            $message .= " for '{$logEntry->task}'";
        }
        return $message;
    }

}
