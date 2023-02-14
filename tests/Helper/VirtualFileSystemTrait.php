<?php
declare(strict_types=1);

namespace Test\Helper;

use org\bovigo\vfs\vfsStream;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
trait VirtualFileSystemTrait
{
    public function setUpFileSystem(): void
    {
        vfsStream::setup();
        mkdir(LOG_DIR);
    }
}
