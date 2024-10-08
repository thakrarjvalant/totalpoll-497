<?php

namespace TotalPollVendors\TotalCore\Contracts\Limitations;
! defined( 'ABSPATH' ) && exit();


/**
 * Interface Bag
 * @package TotalPollVendors\TotalCore\Contracts\Limitations
 */
interface Bag {
	/**
	 * Add limitation.
	 *
	 * @param            $name
	 * @param Limitation $limitation
	 *
	 * @return void
	 */
	public function add( $name, Limitation $limitation );

	/**
	 * Get limitation.
	 *
	 * @param            $name
	 *
	 * @return Limitation|null
	 */
	public function get( $name );

	/**
	 * Remove limitation.
	 *
	 * @param $name
	 *
	 * @return void
	 */
	public function remove( $name );

	/**
	 * Check limitations.
	 *
	 * @return bool
	 */
	public function check();

}