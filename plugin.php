<?php
! defined( 'ABSPATH' ) && exit();

/*
 * Plugin Name: TotalPoll – Pro
 * Plugin URI: https://totalsuite.net/products/totalpoll/
 * Description: Yet another powerful poll plugin for WordPress.
 * Version: 4.9.7
 * Author: TotalSuite
 * Author URI: https://totalsuite.net/
 * Text Domain: totalpoll
 * Domain Path: languages
 * Requires at least: 4.8
 * Requires PHP: 5.6
 * Tested up to: 6.3
 */


if ( defined( 'TOTALPOLL_ROOT' ) ) {
	function _totalpoll_pro_lite_check() {
		$installedPlugins = get_plugins();

		if ( array_key_exists( 'totalpoll-lite/plugin.php', $installedPlugins ) &&
		     array_key_exists( 'totalpoll/plugin.php', $installedPlugins ) ) {
			deactivate_plugins( 'totalpoll-lite/plugin.php', true );
			activate_plugin( 'totalpoll/plugin.php', true );
		}
	}

	add_action( 'shutdown', '_totalpoll_pro_lite_check' );

	return;
}


// Root plugin file name
define( 'TOTALPOLL_ROOT', __FILE__ );

// TotalPoll environment
$env = require dirname( __FILE__ ) . '/env.php';

// Include plugin setup
require_once dirname( __FILE__ ) . '/setup.php';

// Setup
$plugin = new TotalPollSetup( $env );

include 'in-app-assets.php';

// Oh yeah, we're up and running!
