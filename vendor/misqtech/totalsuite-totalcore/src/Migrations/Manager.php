<?php

namespace TotalPollVendors\TotalCore\Migrations;
! defined( 'ABSPATH' ) && exit();


/**
 * Manager
 * @package TotalCore
 * @since   1.0.0
 */
class Manager {
	/**
	 * Migrate Database.
	 */
	public function migrateDatabase() {
		$databaseMigrate = new Database();
		$databaseMigrate->upgrade();
	}
}