<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Day extends StatsCommand
{

    protected static $defaultName = 'day';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Show logged time information for the given day');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from today');
        $this->addArgument('sum', InputArgument::OPTIONAL, 'flag to show the summarize');
    }

    private function getDateTime($offset): \DateTime
    {
        $dateTime = new \DateTime();
        if (is_numeric($offset)) {
            $dateTime->sub(new \DateInterval("P{$offset}D"));
        }
        return $dateTime;
    }

    protected function getDatePeriod($offset): \DatePeriod
    {
        $startDate = $this->getDateTime($offset);
        $endDate = clone($startDate);
        $endDate->add(new \DateInterval('P1D'));
        return new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
    }
}
