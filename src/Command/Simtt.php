<?php
declare(strict_types=1);

namespace Simtt\Command;

use Simtt\Input\Prompter;
use Simtt\Output\Echoer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Simtt extends Command
{

    public const INTERACTIVE_COMMAND_PATTERN = [
        'start' => '(start)(\s+\d{3,4})?(\s+.+)?',
    ];

    protected static $defaultName = 'simtt';

    /** @var Prompter */
    private $prompter;


    public function __construct(Prompter $prompter = null)
    {
        parent::__construct();

        $this->prompter = $prompter ?? Prompter::create(Echoer::create());
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Simtt (only) in interactive mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$command = $this->prompter->prompt('');
        return 0;
    }

}
