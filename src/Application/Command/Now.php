<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\Clock\Clock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Now extends Command
{

    protected static $defaultName = 'now';

    /** @var Clock */
    private $clock;

    /** @var int */
    private $precision;

    public function __construct(Clock $clock, int $precision)
    {
        parent::__construct();
        $this->clock = $clock;
        $this->precision = $precision;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Now shows current time');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $time = Time::now($this->clock);
        $time->roundBy($this->precision);
        $output->writeln('NOW: ' . $this->clock->getDate() . ' ' . $time);
        return 0;
    }
}
