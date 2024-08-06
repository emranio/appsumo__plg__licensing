<?php

namespace AppSumo__GUTENKIT__Licensing;

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

// if class already defined, bail out
if (class_exists('AppSumo__GUTENKIT__Licensing\AppSumoActions')) {
	return;
}


/**
 * This class will handle Api request
 *
 * @package    WPF
 * @subpackage AppSumo__GUTENKIT__Licensing
 * @author     WPFunnels Team <admin@getwpfunnels.com>
 */
class AppSumoActions
{

	/*
	 * @var WP_REST_Request
	 */
	protected $request;
	protected $action;
	protected $prefix;
	protected $appsumo__gutenkit__uuid;

	public function __construct($request, $action)
	{
		$this->request = $request;
		$this->action  = $action;
		$this->appsumo__gutenkit__uuid = sanitize_key($request->get_param('uuid'));
		$this->prefix       = 'appsumo__gutenkit__licensing';
	}


	/**
	 * create order
	 */
	public function create_order()
	{

		$email = $this->request->get_param('activation_email');

		if (empty($email)) {
			return $this->get_error('invalid_request', esc_html__('API request is invalid.', 'appsumo__gutenkit__licensing'));
		}

		$address = array(
			'first_name' => strstr($email, '@', true),
			'email'      => $email,
		);

		$user_exists = get_user_by('email', $email);

		if ($user_exists) {
			return $this->get_error('user_exists', esc_html__('User already exists.', 'appsumo__gutenkit__licensing'));
		}

		$plan_id = sanitize_key($this->request->get_param('plan_id'));
		$appsumo__gutenkit__product_variation = (int) Globals::get_variation_id($plan_id);

		if (!$appsumo__gutenkit__product_variation) {
			return $this->get_error('product_not_defined', esc_html__('Product not defined for Licensing.', 'appsumo__gutenkit__licensing'));
		}

		$password = wp_generate_password();
		$username = Globals::generate_username($email);
		$user_id = wp_create_user($username, $password, $email);
		$user        = get_user_by('email', $email);

		wp_mail(
			$email, 
			'['.Globals::get_product_name().'] Your username and password', 
			Globals::get_confirmation_email_body($email, $username, $password, strstr($email, '@', true)),
			array('Content-Type: text/html; charset=UTF-8')
		);


		update_user_meta($user->ID, 'is_appsumo__gutenkit__user', 'yes');

		if (!$user_id) {
			return $this->get_error('user_not_created', esc_html__('user could not be created.', 'appsumo__gutenkit__licensing'));
		}

		wp_set_current_user($user_id);

		// -----------------------------------------------------------------		
		// starts product purchase creation
		// -----------------------------------------------------------------

		$order_args = array(
			'status'        => 'pending',
			'customer_id'   => $user_id,
			'customer_note' => esc_html__('AppSumo Special Deal', 'appsumo__gutenkit__licensing'),
			'parent'        => null,
			'created_via'   => esc_html__('Created via AppSumo', 'appsumo__gutenkit__licensing'),
			'cart_hash'     => null,
		);


		// Now we create the order
		$order = wc_create_order($order_args);

		if (is_wp_error($order)) {
			return $this->get_error('order_not_created', esc_html__('Order could not be created.', 'appsumo__gutenkit__licensing'));
		}
		$order->add_order_note('AppSumo: Purchase Created. Status changed to pending using AppSumo Integration.');

		// The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
		$order->add_product(wc_get_product($appsumo__gutenkit__product_variation), 1);
		$order->set_address($address, 'billing');

		$order->calculate_totals();
		
		$result = $order->update_status( "completed", esc_html__( 'AppSumo order', 'appsumo__gutenkit__licensing' ), true );


		// Add the note
		$order->add_order_note( '1st time purchase via AppSumo.' );
		$order->add_order_note( 'AppSumo UUID: ' . $this->appsumo__gutenkit__uuid );
		do_action('woocommerce_payment_complete', $order->get_id());

		// -----------------------------------------------------------------
		// ends product purchase creation
		// -----------------------------------------------------------------

		update_post_meta($order->get_id(), 'appsumo__gutenkit__uuid', $this->appsumo__gutenkit__uuid);

		do_action('appsumo__gutenkit__licensing_new_order_created', $order->get_id());

		// now assign the subscription or do additional stuff
		return $result;
	}


	/**
	 * update order
	 */
	public function update_order()
	{
		$email         = $this->request->get_param('activation_email');

		$user = get_user_by('email',  $email);

		if (!$user) {
			return $this->get_error('user_not_found', esc_html__('User Not Found.', 'appsumo__gutenkit__licensing'));
		}

		$plan_id     = sanitize_key($this->request->get_param('plan_id'));
		$max_allowed = Utils::get_max_allowed_for_plan($plan_id);

		if (1 === $max_allowed) {
			return $this->get_error('invalid_plan_id', esc_html__('Plan Id provided is not valid.', 'appsumo__gutenkit__licensing'));
		}

		// starts product purchase creation

		$order_id               = Utils::get_order_id_from_key($this->appsumo__gutenkit__uuid);
		$subscription_id = Utils::get_subscription_id_from_order_id($order_id);

		if (!$order_id || !$subscription_id) {
			return $this->get_error('invalid_uuid', esc_html__('Invalid UUID provide, no subscription found.', 'appsumo__gutenkit__licensing'));
		}
		
		// cancel the order and subscription
		sumo_cancel_subscription($subscription_id);
		
		$appsumo__gutenkit__product_variation    = (int) Globals::get_variation_id($plan_id);
		$address = array(
			'first_name' => strstr($email, '@', true),
			'email'      => $email,
		);
		
		// -----------------------------------------------------------------
		// create order and subscription
		// -----------------------------------------------------------------
		$order_args = array(
			'status'        => 'pending',
			'customer_id'   => $user->ID,
			'customer_note' => esc_html__('AppSumo Special Deal', 'appsumo__gutenkit__licensing'),
			'parent'        => null,
			'created_via'   => esc_html__('Created via AppSumo', 'appsumo__gutenkit__licensing'),
			'cart_hash'     => null,
		);

		// Now we create the order
		$order = wc_create_order($order_args);

		if (is_wp_error($order)) {
			return $this->get_error('order_not_created', esc_html__('Order could not be created.', 'appsumo__gutenkit__licensing'));
		}
		$order->add_order_note('AppSumo: Order Updated from ' . $order_id . ' to ' . $order->get_id() . '. Status changed to pending using AppSumo Integration.');


		$order->add_product(wc_get_product($appsumo__gutenkit__product_variation), 1);
		$order->set_address($address, 'billing');

		$order->calculate_totals();

		$result = $order->update_status("completed", esc_html__('AppSumo order', 'appsumo__gutenkit__licensing'), true);

		// Add the note
		$order->add_order_note('AppSumo Purchase updated to: ' . $plan_id . '-' . $appsumo__gutenkit__product_variation);
		$order->add_order_note('AppSumo UUID: ' . $this->appsumo__gutenkit__uuid);
		do_action('woocommerce_payment_complete', $order->get_id());

		// -----------------------------------------------------------------
		// ends product purchase creation
		// -----------------------------------------------------------------

		update_post_meta($order->get_id(), 'appsumo__gutenkit__uuid', $this->appsumo__gutenkit__uuid);
		do_action('appsumo__gutenkit__licensing_new_order_created', $order->get_id());

		return $result;
	}


	// remove order
	public function remove_order()
	{
		$order_id   = Utils::get_order_id_from_key($this->appsumo__gutenkit__uuid);
		$subscription_id = Utils::get_subscription_id_from_order_id($order_id);

		if (!$order_id) {
			return $this->get_error('subscription_not_found', esc_html__('Purchase could not be found for the key', 'appsumo__gutenkit__licensing'));
		}
		
		// -----------------------------------------------------------------
		sumo_cancel_subscription($subscription_id);
		
		$order = wc_get_order($order_id);
		$order->update_status('refunded');

		// -----------------------------------------------------------------

	
		return true;
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
}
