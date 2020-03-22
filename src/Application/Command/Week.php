<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Week extends StatsCommand
{

    protected static $defaultName = 'week';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Shows logged time information for the given week');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from current week');
        $this->addArgument('sum', InputArgument::OPTIONAL, 'flag to show the summarize');
    }

    protected function getDatePeriod($offset): \DatePeriod
    {
        $startDate = $this->getDateTime($offset);
        $endDate = clone($startDate);
        $endDate->add(new \DateInterval('P7D'));
        return new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
    }

    private function getDateTime($offset): \DateTime
    {
        $dateTime = new \DateTime();
        if ($dateTime->format('w') !== '1') {
            $dateTime->modify('last monday');
        }

        if (is_numeric($offset)) {
            $dateTime->sub(new \DateInterval("P{$offset}W"));
        }
        return $dateTime;
    }
}
