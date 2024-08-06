<?php

namespace Appsumo_Redeem\Traits;

/**
 * Trait for making singleton instance
 *
 * @package Appsumo_Redeem\Traits
 */
trait Singleton {

	private static $instance;

	public static function instance($parem = null) {
		if(!self::$instance) {
			self::$instance = new static();
			
			if($parem != null){
				self::$instance->parem = $parem;
			}
		}

		return self::$instance;
	}
}