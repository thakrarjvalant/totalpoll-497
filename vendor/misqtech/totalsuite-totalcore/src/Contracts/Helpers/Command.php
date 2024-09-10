<?php

namespace TotalPollVendors\TotalCore\Contracts\Helpers;
! defined( 'ABSPATH' ) && exit();


interface Command {

	public static function share( $key, $value );

	public static function getShared( $key, $default );

	public function execute();
}