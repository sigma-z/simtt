<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service\Clock;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
interface Clock
{

    public function getTime(): string;

    public function getDate(): string;
}
