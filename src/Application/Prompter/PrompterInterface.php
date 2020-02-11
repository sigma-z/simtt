<?php
declare(strict_types=1);

namespace Simtt\Application\Prompter;

use Symfony\Component\Console\Output\OutputInterface;

interface PrompterInterface
{
    public function setOutput(OutputInterface $output): void;

    public function prompt(string $message = ''): string;

    public function promptLn(string $message = ''): string;
}
