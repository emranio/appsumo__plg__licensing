<?php

namespace AppSumo__GUTENKIT__Licensing;

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}


class Utils
{
	public static function get_subscription_id_from_order_id($order_id) {
		
		$subscriptions = get_posts(array(
			'limit'        => 1,
			'post_type'    => 'sumosubscriptions',
			'meta_key'     => 'sumo_get_parent_order_id',
			'meta_value'   => $order_id,
			'meta_compare' => '=',
		));

		if (!empty($subscriptions) && !empty($subscriptions[0]->ID)) {
			return $subscriptions[0]->ID;
		} else {
			return false;
		}

	}


	public static function get_order_id_from_key($uuid)
	{

		$orders = wc_get_orders(array(
			'limit'        => 1,
			'meta_key'         => 'appsumo__gutenkit__uuid',
			'meta_value'       => $uuid,
			'meta_compare'  => '=',
			'return'        => 'ids'
		));

		return empty($orders) ? false : $orders[0];
	}

	public static function get_max_allowed_for_plan($plan_id)
	{
		switch ($plan_id):
			case ('appsumo__gutenkit__tier1'):
				$max_allowed = 5;
				break;
			case ('appsumo__gutenkit__tier2'):
				$max_allowed = 15;
				break;
			default:
				$max_allowed = 1000; // unlimited
		endswitch;

		return $max_allowed;
	}
}