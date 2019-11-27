<?php
declare(strict_types=1);

namespace Simtt\Output;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Echoer implements OutputWriter
{

    public static function create(): self
    {
        return new self();
    }

    public function out(string $message): void
    {
        echo $message;
    }

    public function outLn(string $message): void
    {
        $this->out($message . "\n");
    }
}
