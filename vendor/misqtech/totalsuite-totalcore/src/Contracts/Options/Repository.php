<?php

namespace TotalPollVendors\TotalCore\Contracts\Options;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface RepositoryService
 * @package TotalPollVendors\TotalCore\Contracts\Options
 */
interface Repository extends Arrayable, \ArrayAccess, \JsonSerializable {
	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function getOptions();

	/**
	 * Set options.
	 *
	 * @param array $options
	 * @param bool  $persistent
	 *
	 * @return void
	 */
	public function setOptions( $options, $persistent = true );

	/**
	 * Save options.
	 *
	 * @return bool
	 */
	public function save();

	/**
	 * Delete all options.
	 *
	 * @param bool $persistent
	 *
	 * @return bool
	 */
	public function deleteOptions( $persistent = true );

	/**
	 * Get option.
	 *
	 * @param string $option
	 * @param null   $default
	 *
	 * @return mixed
	 */
	public function get( $option, $default = null );

	/**
	 * Set option.
	 *
	 * @param $option
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function set( $option, $value, $persistent = false );
}