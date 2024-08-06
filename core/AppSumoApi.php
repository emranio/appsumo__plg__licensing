<?php

namespace AppSumo__GUTENKIT__Licensing;

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

// if class already defined, bail out
if (class_exists('AppSumo__GUTENKIT__Licensing\AppSumoApi')) {
	return;
}


/**
 * This class will handle Api request
 *
 * @package    WPF
 * @subpackage AppSumo__GUTENKIT__Licensing
 * @author     WPFunnels Team <admin@getwpfunnels.com>
 */
class AppSumoApi
{

	protected $response = array();

	protected $response_code = 200;

	protected $prefix;

	protected $params;

	protected $product;

	protected $woocommerce = null;

	protected $debug = false;

	/*
	 * @var WP_REST_Request
	 */
	protected $request;
	protected $action_class;
	protected $action_name;

	public function __construct()
	{
		$this->prefix       = 'appsumo__gutenkit__licensing';
	}

	// register endpoints for appsumo's notifications/ webhooks
	public function register_rest_route_appsumo__gutenkit__notification_webhook()
	{
		register_rest_route('appsumo__gutenkit__licensing/v1', 'notification', array(
			'methods'  => 'POST',
			'callback' => [$this, 'rest_callback_appsumo__gutenkit__notification'],
			'permission_callback' => '__return_true',
		));

		register_rest_route('jwt-auth/v1', 'token', array(
			'methods'  => 'POST',
			'callback' => [$this, 'rest_callback_jwt_token'],
			'permission_callback' => '__return_true',
		));
	}


	// jwt token to work with appsumo
	public function rest_callback_jwt_token(\WP_REST_Request $request)
	{
		$username = trim($request->get_param('username'));
		$password = $request->get_param('password');

		if(empty($username) || empty($password)){
			return $this->get_error('invalid_request', esc_html__('Empty username or password.', 'appsumo__gutenkit__licensing'));
		}

		$user = get_user_by( 'login', $username );

		if ( !$user || !wp_check_password( $password, $user->data->user_pass, $user->ID) ){
			return $this->get_error('invalid_request', esc_html__('User authentication failed.', 'appsumo__gutenkit__licensing'));
		}

		$token_string = Globals::get_jwt_auth_secret_key() . $user->ID . time();

		require_once (ABSPATH . 'wp-includes/class-phpass.php');
		$wp_hasher = new \PasswordHash(8, FALSE);

		$token = $wp_hasher->HashPassword($token_string);

		set_transient( 'appsumo__gutenkit__licensing_jwt_token_'.$token, time(), 60 * 60 * 2 );

		return array(
			// 'length' =>strlen($token),
			// 'version' => '1.0.0',
			'access' => $token
		);
	}


	// WP REST API callback
	public function rest_callback_appsumo__gutenkit__notification(\WP_REST_Request $request)
	{

		$this->request = $request;
		$token_validation = $this->validate_jwt_token();

		if (is_wp_error($token_validation)) {
			return rest_ensure_response($token_validation);
		}

		$this->action_name = sanitize_key($this->request->get_param('action'));

		if (empty($this->request->get_param('uuid'))) {
			return $this->get_error('invalid_request', esc_html__('MISSING_UUID API request is invalid.', 'appsumo__gutenkit__licensing'));
		}

		$this->action_class = new \AppSumo__GUTENKIT__Licensing\AppSumoActions($request, $this->action_name);

		switch ($this->action_name):
			case ('activate'):
				$response = $this->endpoint_activate();
				break;
			case ('enhance_tier'):
			case ('reduce_tier'):
				$response = $this->endpoint_update_plan();
				break;
			case ('refund'):
				$response = $this->endpoint_refund();
				break;
			default:
				return $this->endpoint_not_found();

		endswitch;

		return apply_filters('appsumo__gutenkit__licensing_notification_endpoint_response', $response);
	}


	/**
	 * validate JWT token
	 */
	public function validate_jwt_token()
	{
		$token = ltrim(sanitize_text_field( $_SERVER['HTTP_AUTHORIZATION'] ?? '' ), 'Bearer ');
		if(empty($token) || strlen($token) != 60){
			return $this->get_error('empty_invalid_token', esc_html__('Empty or invalid token', 'appsumo__gutenkit__licensing'));
		}
		
		$token_transient = get_transient( 'appsumo__gutenkit__licensing_jwt_token_'.$token );
		if(empty($token_transient)){
			return $this->get_error('invalid_token', esc_html__('Invalid token', 'appsumo__gutenkit__licensing'));
		}

		return true;
	}



	/**
	 * activate action
	 */
	public function endpoint_activate()
	{

		// make sure a license is created and
		$order_created = $this->action_class->create_order();

		if (is_wp_error($order_created)) {
			$response           = new \WP_REST_Response();
			$data               = new \stdClass();
			$data->message      = $order_created;
			$response->set_status(200);
			$response->set_data($data);
			return rest_ensure_response($order_created);
		}

		require_once (ABSPATH . 'wp-includes/class-phpass.php');
		$wp_hasher = new \PasswordHash(8, FALSE);
	
		$password = $this->request->get_param('activation_email');
		$hashedPassword = $wp_hasher->HashPassword($password);

		$redirect_url = esc_url_raw( get_config('appsumo__gutenkit__redirect_link'));
		$redirect_url .= '?'.\AppSumo__GUTENKIT__Licensing\Globals::get_request_key().'='.\AppSumo__GUTENKIT__Licensing\Globals::get_request_value();
		$redirect_url .= '&email=' . $this->request->get_param('activation_email');
		$redirect_url .= '&token=' . $hashedPassword;

		$data               = new \stdClass();
		$data->message      = esc_html__('User Account and License created', 'appsumo__gutenkit__licensing');
		$data->redirect_url = $redirect_url;

		$response = new \WP_REST_Response();
		$response->set_status(201);
		$response->set_data($data);

		return rest_ensure_response($response);
	}



	/**
	 * plan update with API action
	 */
	public function endpoint_update_plan()
	{

		$update_plan = $this->action_class->update_order();

		if (is_wp_error($update_plan) || !$update_plan) {
			return $update_plan;
		}

		$data          = new \stdClass();
		$data->message = esc_html__('AppSumo-'.\AppSumo__GUTENKIT__Licensing\Globals::get_product_name() .' Plan Updated.', 'appsumo__gutenkit__licensing');

		$response = new \WP_REST_Response();
		$response->set_status(200);
		$response->set_data($data);

		return rest_ensure_response($response);
	}




	/**
	 * api action for refund
	 */
	public function endpoint_refund()
	{


		// make sure a license is created and
		$remove_subscription = $this->action_class->remove_order();

		if (is_wp_error($remove_subscription)) {
			return $remove_subscription;
		}

		$data          = new \stdClass();
		$data->message = esc_html__('Product refunded. User Account and License removed', 'appsumo__gutenkit__licensing');

		$response = new \WP_REST_Response();
		$response->set_status(200);
		$response->set_data($data);

		return rest_ensure_response($response);
	}

	/**
	 * api callback not found exception
	 *
	 */
	public function endpoint_not_found()
	{

		$error = $this->get_error('endpoint_not_found', esc_html__('No such API action found.', 'appsumo__gutenkit__licensing'));

		return rest_ensure_response($error);
	}

	public function get_error($code, $message, $status_code = 403)
	{

		return new \WP_Error(
			$code,
			$message,
			array(
				'status' => $status_code,
			)
		);
	}


	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function debug($var)
	{
		echo wp_send_json($var);
		die();
	}
}
