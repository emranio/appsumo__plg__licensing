<?php 
namespace Appsumo_PLG_Licensing;

class Env
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        // Load the config file
        $configFile = __DIR__ . '/../config.php';
        if (file_exists($configFile)) {
            $fileConfig = include $configFile;
            if (is_array($fileConfig)) {
                $this->config = array_merge($this->config, $fileConfig);
            }
        }
    }

    public static function get($key)
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return  self::$instance->config[$key] ?? null;
    }
}