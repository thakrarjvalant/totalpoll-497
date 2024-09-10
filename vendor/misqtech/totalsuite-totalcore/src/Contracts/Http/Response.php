<?php

namespace TotalPollVendors\TotalCore\Contracts\Http;
! defined( 'ABSPATH' ) && exit();


/**
 * Interface Response
 * @package TotalPollVendors\TotalCore\Contracts\Http
 */
interface Response {
	/**
	 * Send response.
	 *
	 * @return $this
	 */
	public function send();
}