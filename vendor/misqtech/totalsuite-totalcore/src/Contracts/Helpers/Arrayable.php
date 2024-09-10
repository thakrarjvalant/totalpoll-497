<?php

namespace TotalPollVendors\TotalCore\Contracts\Helpers;
! defined( 'ABSPATH' ) && exit();


/**
 * Interface Arrayable
 * @package TotalPollVendors\TotalCore\Contracts\Helpers
 */
interface Arrayable {

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray();

}