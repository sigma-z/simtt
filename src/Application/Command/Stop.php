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

    protected function getMessageForActionPerformed(LogEntry $logEntry, bool $isPersisted, InputInterface $input): string
    {
        $message = $this->isUpdate($input) && $isPersisted
            ? 'Timer stop updated to ' . $logEntry->stopTime
            : 'Timer stopped at ' . $logEntry->stopTime;
        if ($logEntry->task) {
            $message .= " for '{$logEntry->task}'";
        }
        return $message;
    }

}
