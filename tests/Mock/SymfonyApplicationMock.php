<?php
declare(strict_types=1);

namespace Mock;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyApplicationMock extends Application
{
    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
    }
}
