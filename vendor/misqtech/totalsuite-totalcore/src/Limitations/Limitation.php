<?php

namespace TotalPollVendors\TotalCore\Limitations;
! defined( 'ABSPATH' ) && exit();



use TotalPollVendors\TotalCore\Contracts\Limitations\Limitation as LimitationContract;

/**
 * Class Limitation
 * @package TotalPollVendors\TotalCore\Limitations
 */
abstract class Limitation implements LimitationContract {
	/**
	 * @var array $args
	 */
	protected $args = [];

	/**
	 * Limitation constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		$this->args = $args;
	}
}