<?php
declare(strict_types=1);

namespace Simtt\Command;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Parser
{

    public function parse(string $pattern, string $input)
    {
        preg_match("/$pattern/", $input, $matches);
        if ($matches) {
            array_shift($matches);
        }
        if ($matches) {
            $command = trim(array_shift($matches));
            $args = array_map(static function(string $item) {
                return trim($item);
            }, $matches);
            return new ParseResult($command, $args);
        }
        return false;
    }
}
