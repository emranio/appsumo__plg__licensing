<?php
/**
 * Plugin Name: Appsumo PLG Licensing
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

// Autoload Composer dependencies
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

// Plugin code starts here

// write log to debug.log

if (!function_exists('write_log')) {
    function write_log(...$data)
    {
        $log_file = plugin_dir_path(__FILE__) . '/debug.log';
        foreach ($data as $key => $log) {
            if (is_array($log) || is_object($log)) {
                file_put_contents($log_file, print_r("$$key >> \n$log", true) . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents($log_file, "$$key >> \n$log" . PHP_EOL, FILE_APPEND);
            }
        }
    }
}

// Load plugin textdomain
add_action('plugins_loaded', 'appsumo_plg_licensing_load_textdomain');
function appsumo_plg_licensing_load_textdomain()
{
    load_plugin_textdomain('appsumo_plg_licensing', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Load plugin settings from config file
add_action('plugins_loaded', function(){
    try {
        new Appsumo_PLG_Licensing\Init();
    } catch (Exception $e) {
        write_log("Caught exception in ParentClass: " . $e->getMessage());
    } catch (Error $e) {
        write_log("hello function fatal error: " . $e->getMessage());
    }
});

add_action('init', function(){
    try {
        new Appsumo_PLG_Licensing\AutoLogin();
    } catch (Exception $e) {
        write_log("Caught exception in ParentClass: " . $e->getMessage());
    } catch (Error $e) {
        write_log("hello function fatal error: " . $e->getMessage());
    }
});


