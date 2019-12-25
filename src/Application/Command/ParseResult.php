<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Symfony\Component\Console\Input\StringInput;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ParseResult
{

    /** @var string */
    private $commandName;

    /** @var array */
    private $args;


    public function __construct(array $argv)
    {
        if (!isset($argv[0])) {
            throw new \RuntimeException('Missing command name in arguments.');
        }
        $this->commandName = array_shift($argv);
        $this->args = $argv;
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function isExitCommand(): bool
    {
        return in_array($this->commandName, ['exit', 'quit', 'q'], true);
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function createInput(): StringInput
    {
        $arguments = $this->getArgs();
        array_unshift($arguments, $this->getCommandName());
        return new StringInput(implode(' ', $arguments));
    }

}
