<?php

namespace TotalPollVendors\TotalCore\Helpers;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Contracts\Helpers\Command as CommandContract;

/**
 * Class Command
 * @package TotalPollVendors\TotalCore\Helpers
 */
abstract class Command implements CommandContract {
	protected static $shared = [];

	/**
	 * Share value across commands.
	 *
	 * @param $key
	 * @param $value
	 */
	public static function share( $key, $value ) {
		static::$shared[ $key ] = $value;
	}

	/**
	 * Get shared value.
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public static function getShared( $key, $default = null ) {
		return Arrays::getDotNotation( static::$shared, $key, $default );
	}

	/**
	 * Execute the command.
	 *
	 * @param null $previousCommandResult
	 *
	 * @return null
	 */
	public function execute( $previousCommandResult = null ) {
		if ( $previousCommandResult instanceof \WP_Error ):
			return $previousCommandResult;
		endif;

		return $this->handle();
	}

	/**
	 * Command logic.
	 *
	 * @return mixed
	 */
	abstract protected function handle();
}