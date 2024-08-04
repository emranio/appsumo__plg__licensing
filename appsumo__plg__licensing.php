<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       AppSumo __plg__ Licensing
 * Plugin URI:        #
 * Description:       Connects with appsumo api to create new user and package in woocommerce and EDD.
 * Version:           1.0.0
 * Author:            #
 * Author URI:        #
 * License:           GPL-3
 * Text Domain:       appsumo__plg__licensing
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class AppSumo_Licensing
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

		$license_api = new AppSumo__PLG__Licensing\AppSumoApi();
		add_action('rest_api_init', array($license_api, 'register_rest_route_appsumo__plg__notification_webhook'));

		new \AppSumo__PLG__Licensing\UserForm();
	}
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action('plugins_loaded', function () {
	AppSumo_Licensing::boot();
});

add_action('init', function () {
	if (!isset($_GET[\AppSumo__PLG__Licensing\Globals::get_request_key()]) || $_GET[\AppSumo__PLG__Licensing\Globals::get_request_key()] != \AppSumo__PLG__Licensing\Globals::get_request_value()) {
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
	$is_appsumo__plg__user = get_user_meta($user->ID, 'is_appsumo__plg__user', true);
	$is_appsumo__plg__user_logged_in = get_user_meta($user->ID, 'is_appsumo__plg__user_logged_in', true);
	if ($is_appsumo__plg__user != 'yes' || $is_appsumo__plg__user_logged_in == 'yes') {
		return;
	}

	// start procedure to login the user
	if (is_user_logged_in()) {
		wp_logout();
	}

	// hook in earlier than other callbacks to short-circuit them
	add_filter('authenticate', 'appsumo__plg__licensing_allow_auto_login', 10, 3);

	$user = wp_signon(array('user_login' => $user->user_login), true);
	remove_filter('authenticate', 'appsumo__plg__licensing_allow_auto_login', 10);

	if (is_a($user, 'WP_User')) {
		wp_set_current_user($user->ID, $user->user_login);
		if (is_user_logged_in()) {
			update_user_meta($user->ID, 'is_appsumo__plg__user_logged_in', 'yes');
			wp_redirect(esc_url_raw(\AppSumo__PLG__Licensing\Globals::get_appsumo__plg__redirect_link()));
		}
	}
}, 10, 0);

function appsumo__plg__licensing_allow_auto_login($user, $username, $password)
{
	return get_user_by('login', $username);
}
