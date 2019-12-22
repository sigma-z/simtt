<?php
declare(strict_types=1);

namespace Simtt\Application\Config;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Config
{

    /** @var string */
    private $logDir;

    /** @var int */
    private $precision = 1;

    /** @var int */
    private $showLogItems = 15;

    /** @var int */
    private $showTaskItems = 15;

    public function setLogDir(string $logDir): void
    {
        $this->logDir = $logDir;
    }

    public function getLogDir(): string
    {
        return $this->logDir;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getShowLogItems(): int
    {
        return $this->showLogItems;
    }

    public function getShowTaskItems(): int
    {
        return $this->showTaskItems;
    }
}
