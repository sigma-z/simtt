<?php
declare(strict_types=1);

namespace Test\Helper;

use Vfs\FileSystem;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class VirtualFileSystem
{

    public const LOG_DIR = 'vfs://logs';
    public const SCHEME = 'vfs://';

    /** @var FileSystem */
    private static $fs;

    public static function setUpFileSystem(): void
    {
        //echo 'setUp ' . debug_backtrace()[1]['class'] . "\n";

        self::$fs = FileSystem::factory(self::SCHEME);
        self::$fs->mount();
        mkdir(self::LOG_DIR);
    }

    public static function tearDownFileSystem(): void
    {
        //echo 'tearDown ' . debug_backtrace()[1]['class'] . "\n";

        self::$fs->unmount();
    }
}
