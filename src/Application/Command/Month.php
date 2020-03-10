<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Month extends StatsCommand
{

    protected static $defaultName = 'month';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Show logged time information for the given month');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from current month');
        $this->addArgument('sum', InputArgument::OPTIONAL, 'flag to show the summarize');
    }

    protected function getDatePeriod($offset): \DatePeriod
    {
        $startDate = $this->getDateTime($offset);
        $endDate = clone($startDate);
        $daysOfMonth = $startDate->format('t');
        $endDate->add(new \DateInterval("P{$daysOfMonth}D"));
        return new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
    }

    private function getDateTime($offset): \DateTime
    {
        $dateTime = new \DateTime();
        if ($dateTime->format('d') !== '1') {
            $dateTime->modify('first day of');
        }

        if (is_numeric($offset)) {
            $dateTime->sub(new \DateInterval("P{$offset}M"));
        }
        return $dateTime;
    }
}
