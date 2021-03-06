<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\LogEntry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Comment extends PropertyUpdateCommand
{

    protected static $defaultName = 'comment';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Sets comment for current log entry by offset, if given');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from last log entry of today', 0);
        $this->addArgument('comment', InputArgument::OPTIONAL, 'comment');
    }

    protected function processInputArguments(InputInterface $input): void
    {
        $offset = $input->getArgument('offset');
        if ($offset && !is_numeric($offset) && !$input->getArgument('comment')) {
            $input->setArgument('offset', 0);
            $input->setArgument('comment', $offset);
        }
    }

    protected function getMessageForActionPerformed(LogEntry $logEntry): string
    {
        $message = "Comment '{$logEntry->comment}' updated for log started at {$logEntry->startTime}";
        if ($logEntry->task) {
            $message .= " for '{$logEntry->task}'";
        }
        return $message;
    }
}
