<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       AppSumo __gutenkit__ Licensing
 * Plugin URI:        #
 * Description:       Connects with appsumo api to create new user and package in woocommerce and EDD.
 * Version:           1.0.0
 * Author:            #
 * Author URI:        #
 * License:           GPL-3
 * Text Domain:       appsumo__gutenkit__licensing
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class AppSumo_Licensing__PLG__
{
	public static function get_version()
	{
		return '1.0.0';
	}

	public static function get_plugin_dir()
	{
		return plugin_dir_path(__FILE__);
	}

	public static function boot()
	{
		include_once self::get_plugin_dir() . 'vendor/autoload.php';

		$license_api = new AppSumo__GUTENKIT__Licensing\AppSumoApi();
		add_action('rest_api_init', array($license_api, 'register_rest_route_appsumo__gutenkit__notification_webhook'));

		new \AppSumo__GUTENKIT__Licensing\UserForm();
	}

	public static function auto_login()
	{

		if (!isset($_GET[\AppSumo__GUTENKIT__Licensing\Globals::get_request_key()]) || $_GET[\AppSumo__GUTENKIT__Licensing\Globals::get_request_key()] != \AppSumo__GUTENKIT__Licensing\Globals::get_request_value()) {
			return;
		}

		$email = $_GET['email'] ?? '';
		$token = $_GET['token'] ?? '';
		if (empty($email) || empty($token)) {
			return;
		}

		require_once(ABSPATH . 'wp-includes/class-phpass.php');
		$wp_hasher = new \PasswordHash(8, FALSE);
		$check = $wp_hasher->CheckPassword($email, $token);
		if (!$check) {
			return;
		}

		$user = get_user_by('email', $email);
		if (!$user) {
			return;
		}

		// check and return if it's an old user.
		$is_appsumo__gutenkit__user = get_user_meta($user->ID, 'is_appsumo__gutenkit__user', true);
		$is_appsumo__gutenkit__user_logged_in = get_user_meta($user->ID, 'is_appsumo__gutenkit__user_logged_in', true);
		if ($is_appsumo__gutenkit__user != 'yes' || $is_appsumo__gutenkit__user_logged_in == 'yes') {
			return;
		}

		// start procedure to login the user
		if (is_user_logged_in()) {
			wp_logout();
		}

		// hook in earlier than other callbacks to short-circuit them
		add_filter('authenticate', static, 'allow_auto_login', 10, 3);

		$user = wp_signon(array('user_login' => $user->user_login), true);
		remove_filter('authenticate', [static, 'allow_auto_login'], 10);

		if (is_a($user, 'WP_User')) {
			wp_set_current_user($user->ID, $user->user_login);
			if (is_user_logged_in()) {
				update_user_meta($user->ID, 'is_appsumo__gutenkit__user_logged_in', 'yes');
				wp_redirect(esc_url_raw(\AppSumo__GUTENKIT__Licensing\Globals::get_appsumo__gutenkit__redirect_link()));
			}
		}
	}

	function allow_auto_login($user, $username, $password)
	{
		return get_user_by('login', $username);
	}
}


function write_log(...$log)
{
	if (true === WP_DEBUG) {
		foreach ($log as $key => $data) {
			if (is_array($data) || is_object($data)) {
				error_log("$$key >> \n" . print_r($data, true));
			} else {
				error_log("$$key >> \n" . $data);
			}
		}
	}
}

add_action('plugins_loaded', function () {
	try {
		AppSumo_Licensing__PLG__::boot();
	} catch (Exception $e) {
		write_log("Caught exception in ParentClass: " . $e->getMessage());
	} catch (Error $e) {
		write_log("hello function fatal error: " . $e->getMessage());
	}
});

add_action('init', function () {
	try {
		AppSumo_Licensing__PLG__::auto_login();
	} catch (Exception $e) {
		write_log("Caught exception in ParentClass: " . $e->getMessage());
	} catch (Error $e) {
		write_log("hello function fatal error: " . $e->getMessage());
	}
}, 10, 0);
