<?php
declare(strict_types=1);

namespace Simtt\Output;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
interface OutputWriter
{

    public function out(string $message): void;

    public function outLn(string $message): void;

}
