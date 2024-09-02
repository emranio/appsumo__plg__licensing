<?php 
namespace Appsumo_PLG_Licensing;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Class Env
|--------------------------------------------------------------------------
|
| This class is responsible for loading and providing access to the 
| configuration settings for the Appsumo PLG Licensing plugin. It uses 
| a singleton pattern to ensure that the configuration is loaded only once.
|
*/
class Env
{
    // Singleton instance of the Env class
    private static $instance = null;

    // Array to store the configuration settings
    private $config = [];

    /**
     * Private constructor to prevent direct instantiation.
     *
     * This constructor loads the configuration settings from the config.php file
     * and merges them with the existing configuration array.
     */
    private function __construct()
    {
        // Path to the config file
        $configFile = __DIR__ . '/../config.php';

        // Check if the config file exists
        if (file_exists($configFile)) {
            // Include the config file and store its contents in $fileConfig
            $fileConfig = include $configFile;

            // If the config file returns an array, merge it with the existing config array
            if (is_array($fileConfig)) {
                $this->config = array_merge($this->config, $fileConfig);
            }
        }
    }

    /**
     * Get the value of a configuration setting by key.
     *
     * @param string $key The key of the configuration setting.
     * @return mixed|null The value of the configuration setting, or null if not found.
     */
    public static function get($key)
    {
        // If the singleton instance is not created yet, create it
        if (self::$instance === null) {
            self::$instance = new self();
        }

        // Return the value of the configuration setting, or null if not found
        return self::$instance->config[$key] ?? null;
    }
}