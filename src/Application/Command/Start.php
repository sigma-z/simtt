<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\LogEntry;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Start extends TimerCommand
{

    protected static $defaultName = 'start';

    protected function configure(): void
    {
        parent::configure();

        $this->setAliases(['start*']);
        $this->setDescription('Starts a timer');
    }

    protected function getMessageForActionPerformed(LogEntry $logEntry, bool $isPersisted, InputInterface $input): string
    {
        $message = $this->isUpdate($input) && $isPersisted
            ? 'Timer start updated to ' . $logEntry->startTime
            : 'Timer started at ' . $logEntry->startTime;
        if ($logEntry->task) {
            $message .= " for '{$logEntry->task}'";
        }
        return $message;
    }

}
