<?php

namespace TotalPoll\Admin\Upgrade;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Admin\Pages\Page as TotalCoreAdminPage;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Page
 * @package TotalPoll\Admin\Upgrade
 */
class Page extends TotalCoreAdminPage {

	/**
	 * Page assets.
	 */
	public function assets() {
		wp_enqueue_style( 'totalpoll-admin-upgrade-to-pro' );
	}

	/**
	 * Page content.
	 */
	public function render() {
		Tracking::trackScreens('upgrade');
		include __DIR__ . '/views/index.php';
	}
}