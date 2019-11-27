<?php
declare(strict_types=1);

namespace Simtt\Input;

use Simtt\Output\OutputWriter;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Prompter
{

    /** @var resource */
    private $stream;

    /** @var OutputWriter */
    private $outputWriter;

    private function __construct($stream, OutputWriter $outputWriter)
    {
        if (!is_resource($stream)) {
            throw new \RuntimeException('Expected stream resource');
        }
        $this->stream = $stream;
        $this->outputWriter = $outputWriter;
    }

    public function __destruct()
    {
        fclose($this->stream);
    }

    public static function create(OutputWriter $outputWriter): self
    {
        $stream = fopen('php://stdin','rb', false);
        return new self($stream, $outputWriter);
    }

    public function prompt(string $message = ''): string
    {
        $this->outputWriter->out($message);
        $stdin = fgets($this->stream);
        return trim($stdin);
    }

    public function promptLn(string $message = ''): string
    {
        return $this->prompt($message . "\n");
    }

}
