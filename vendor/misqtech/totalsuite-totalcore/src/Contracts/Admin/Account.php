<?php

namespace TotalPollVendors\TotalCore\Contracts\Admin;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Account service class
 * @package TotalPollVendors\TotalCore\Admin
 */
interface Account extends Arrayable, \JsonSerializable {
	/**
	 * Get account status.
	 *
	 * @return bool
	 */
	public function isLinked();

	/**
	 * Get account key.
	 *
	 * @return string
	 */
	public function getAccessToken();

	/**
	 * Get account email.
	 *
	 * @return string
	 */
	public function getEmail();

	/**
	 * Get account.
	 *
	 * @return false|array
	 */
	public function get();

	/**
	 * Set account email.
	 *
	 * @param array $account
	 *
	 * @return bool
	 */
	public function set( $account );

	/**
	 * Check account access token.
	 *
	 * @param string $accessToken
	 *
	 * @return array|\WP_Error
	 */
	public function checkAccessToken( $accessToken );

}