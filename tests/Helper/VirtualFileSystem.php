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

    /** @var FileSystem */
    private static $fs;

    public static function setUpFileSystem(): void
    {
        self::$fs = FileSystem::factory('vfs://');
        self::$fs->mount();
        mkdir(self::LOG_DIR);
    }

    public static function tearDownFileSystem(): void
    {
        self::$fs->unmount();
    }
}
