<?php
declare(strict_types=1);

namespace Simtt\Domain;

use Simtt\Domain\Model\LogFileInterface;

interface LogFileFinderInterface
{
    public function getLastLogFile(): ?LogFileInterface;

    public function getLogFileForDate(\DateTime $dateTime): LogFileInterface;

    public function getLogFiles(): array;

    public function getPath(): string;
}
