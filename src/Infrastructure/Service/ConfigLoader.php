<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Application\Config\Config;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ConfigLoader
{
    public static function load(string $configFile, string $rootDir): Config
    {
        $configFile = self::getConfigFile($configFile);
        $data = [];
        if ($configFile) {
            $json = file_get_contents($configFile);
            $data = json_decode($json, true);
            if (!is_array($data)) {
                $data = [];
            }
        }
        return new Config($data, $rootDir);
    }

    private static function getConfigFile(string $configFile): string
    {
        if (is_file($configFile)) {
            return $configFile;
        }
        if (is_file($configFile . '.dist')) {
            return $configFile . '.dist';
        }
        return '';
    }
}
