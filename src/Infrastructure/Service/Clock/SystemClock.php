<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service\Clock;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class SystemClock implements Clock
{

    public function getTime(): string
    {
        return (new \DateTime())->format('H:i');
    }

    public function getDate(): string
    {
        return (new \DateTime())->format('Y-m-d');
    }
}
