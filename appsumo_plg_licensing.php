<?php
/**
 * Plugin Name: Appsumo Plg Licensing
 * Plugin URI: https://example.com/appsumo-plg-licensing
 * Description: Appsumo v2 licensing plugin for WordPress
 * Version: 1.0.0
 * Author: Emran
 * Author URI: mailto:emranio@yahoo.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: appsumo_plg_licensing
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit(); // Exit if accessed directly

// Autoload Composer dependencies
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

// Plugin code starts here

/**
 * Write log to debug.log
 *
 * This function writes log messages to the debug.log file located in the wp-content directory.
 * It accepts multiple data arguments and writes each one to the log file.
 *
 * @param mixed ...$data The data to be logged.
 * @return void
 */
if (!function_exists('write_log')) {
    function write_log(...$data)
    {
        $log_file = WP_CONTENT_DIR . '/debug.log'; // Path to the debug.log file
        foreach ($data as $key => $log) {
            if (is_array($log) || is_object($log)) {
                // If the log is an array or object, print it in a readable format
                file_put_contents($log_file, "$$key >> \n" . print_r($log, true) . PHP_EOL, FILE_APPEND);
            } else {
                // If the log is a scalar value, write it directly
                file_put_contents($log_file, "$$key >> \n$log" . PHP_EOL, FILE_APPEND);
            }
        }
    }
}

/**
 * Load plugin textdomain
 *
 * This function loads the plugin's translated strings for localization.
 * It is hooked to the 'plugins_loaded' action.
 *
 * @return void
 */
add_action('plugins_loaded', 'appsumo_plg_licensing_load_textdomain');
function appsumo_plg_licensing_load_textdomain()
{
    load_plugin_textdomain('appsumo_plg_licensing', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Load plugin settings from config file
 *
 * This function initializes the plugin by creating an instance of the Init class.
 * It is hooked to the 'plugins_loaded' action.
 *
 * @return void
 */
add_action('plugins_loaded', function(){
    try {
        new Appsumo_PLG_Licensing\Init(); // Initialize the plugin
    } catch (Exception $e) {
        write_log("Caught exception in ParentClass: " . $e->getMessage()); // Log exceptions
    } catch (Error $e) {
        write_log("hello function fatal error: " . $e->getMessage()); // Log errors
    }
});

// test only
add_action('init', function(){
    try {
        // new Appsumo_PLG_Licensing\AutoLogin(); // Initialize AutoLogin
    } catch (Exception $e) {
        write_log("Caught exception in ParentClass: " . $e->getMessage()); // Log exceptions
    } catch (Error $e) {
        write_log("hello function fatal error: " . $e->getMessage()); // Log errors
    }
});