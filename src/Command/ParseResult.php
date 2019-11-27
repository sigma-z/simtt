<?php
declare(strict_types=1);

namespace Simtt\Command;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ParseResult
{

    /** @var string */
    private $commandName;

    /** @var array */
    private $args;


    public function __construct(string $commandName, array $args)
    {
        $this->commandName = $commandName;
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

}
