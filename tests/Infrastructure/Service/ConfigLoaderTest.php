<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   24.12.19
 */

namespace Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\ConfigLoader;
use Test\Helper\VirtualFileSystem;

class ConfigLoaderTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
    }

    protected function tearDown(): void
    {
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testLoadFallbackToDist(): void
    {
        $configData = [
            'logDir' => VirtualFileSystem::LOG_DIR
        ];
        file_put_contents(VirtualFileSystem::SCHEME . 'config.json.dist', json_encode($configData));
        $config = ConfigLoader::load(VirtualFileSystem::SCHEME . 'config.json', APP_ROOT);
        self::assertSame(VirtualFileSystem::LOG_DIR, $config->getLogDir());
    }

}
