<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Prompter;

use Simtt\Application\Prompter\PrompterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Prompter implements PrompterInterface
{

    /** @var resource */
    private $stream;

    /** @var OutputInterface */
    private $output;

    private function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new \RuntimeException('Expected stream resource');
        }
        $this->stream = $stream;
    }

    public function __destruct()
    {
        fclose($this->stream);
    }

    public static function create(): self
    {
        $stream = fopen('php://stdin','rb', false);
        return new self($stream);
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function prompt(string $message = ''): string
    {
        $this->output->write($message);
        $stdin = fgets($this->stream);
        return trim($stdin);
    }

    public function promptLn(string $message = ''): string
    {
        return $this->prompt($message . "\n");
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
