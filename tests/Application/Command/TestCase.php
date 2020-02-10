<?php
declare(strict_types=1);

namespace Test\Application\Command;

use Helper\DIContainer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    abstract protected function getCommandShortName(): string;

    protected function setUp(): void
    {
        DIContainer::$container->reset();
        parent::setUp();
    }

    protected function runCommand(string $stringInput): BufferedOutput
    {
        return $this->_runCommand($stringInput, false);
    }

    protected function runCommandInInteractiveMode(string $stringInput): BufferedOutput
    {
        return $this->_runCommand($stringInput, true);
    }

    protected function _runCommand(string $stringInput, bool $interactive): BufferedOutput
    {
        $application = new Application('Simtt');
        $application->setAutoExit(false);
        /** @noinspection PhpParamsInspection */
        $application->add(DIContainer::$container->get($this->getCommandShortName()));
        $input = new StringInput($stringInput);
        $input->setInteractive($interactive);
        $output = new BufferedOutput();
        $application->run($input, $output);
        return $output;
    }
}
