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
    private $precision;

    /** @var int */
    private $showLogItems;

    /** @var int */
    private $showTaskItems;

    public function __construct(array $data = [])
    {
        $this->logDir = $data['logDir'] ?? './logs';
        $this->precision = $data['precision'] ?? 1;
        $this->showLogItems = $data['showLogItems'] ?? 15;
        $this->showTaskItems = $data['showTaskItems'] ?? 15;
    }

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
