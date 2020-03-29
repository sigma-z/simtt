<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service\Clock;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class FixedClock implements Clock
{

    /** @var \DateTime */
    private $dateTime;

    public function __construct(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getTime(): string
    {
        return $this->dateTime->format('H:i');
    }

    public function getDate(): string
    {
        return $this->dateTime->format('Y-m-d');
    }

}
