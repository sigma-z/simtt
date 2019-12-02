<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Parser
{

    /** @var array */
    private $patterns;

    public function __construct(array $patterns)
    {
        $this->patterns = $patterns;
    }

    public function parse(string $input)
    {
        foreach ($this->patterns as $pattern) {
            $result = $this->match($pattern, $input);
            if ($result) {
                return $result;
            }
        }
        return false;
    }

    private function match(string $pattern, string $input)
    {
        if (empty($pattern) || empty($input)) {
            return false;
        }

        preg_match("/$pattern/", trim($input), $matches);
        if ($matches) {
            array_shift($matches);
        }
        if ($matches) {
            $command = trim(array_shift($matches));
            $args = array_map(static function (string $item) {
                return trim($item);
            }, $matches);
            $args = array_values(array_filter($args));
            return new ParseResult($command, $args);
        }
        return false;
    }
}
