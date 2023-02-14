<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   24.12.19
 */

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\ConfigLoader;
use Test\Helper\VirtualFileSystemTrait;

class ConfigLoaderTest extends TestCase
{
    use VirtualFileSystemTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
    }

    public function testLoadFallbackToDist(): void
    {
        $configData = [
            'logDir' => LOG_DIR
        ];
        file_put_contents('vfs://root/config.json.dist', json_encode($configData));
        $config = ConfigLoader::load('vfs://root/config.json', APP_ROOT);
        self::assertSame(LOG_DIR, $config->getLogDir());
    }

}
