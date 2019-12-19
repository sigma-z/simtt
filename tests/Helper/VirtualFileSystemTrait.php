<?php
declare(strict_types=1);

namespace Test\Helper;

use Vfs\FileSystem;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
trait VirtualFileSystemTrait
{

    /** @var FileSystem */
    private static $fs;

    /**
     * @beforeClass
     */
    public static function setUpFileSystem(): void
    {
        self::$fs = FileSystem::factory('vfs://');
        self::$fs->mount();
    }

    /**
     * @afterClass
     */
    public static function tearDownFileSystem(): void
    {
        self::$fs->unmount();
    }
}
