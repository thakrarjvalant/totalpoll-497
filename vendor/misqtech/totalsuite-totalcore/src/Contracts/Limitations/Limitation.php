<?php

namespace TotalPollVendors\TotalCore\Contracts\Limitations;
! defined( 'ABSPATH' ) && exit();


/**
 * Interface Limitation
 * @package TotalPollVendors\TotalCore\Contracts\Limitations
 */
interface Limitation {
	/**
	 * Limitation logic.
	 *
	 * @return bool
	 */
	public function check();
}