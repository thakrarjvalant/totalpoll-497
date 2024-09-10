<?php

namespace TotalPoll\Admin\Plugins;
! defined( 'ABSPATH' ) && exit();


class UninstallFeedback {
	public function __construct() {
		add_action( 'pre_current_active_plugins', [ $this, 'row' ] );
		add_action( 'wp_ajax_uninstall_feedback_for_' . TotalPoll( 'env' )->get( 'slug' ), [ $this, 'collect' ] );
	}

	public function row() {
		$product  = TotalPoll( 'env' )->get( 'slug' );
		$basename = TotalPoll( 'env' )->get( 'basename' );
		include 'views/uninstall-feedback.php';
	}

	public function collect() {
		if ( current_user_can( 'manage_options' ) ) {
			$feedback            = TotalPoll( 'http.request' )->request( 'feedback' );
			$feedback['product'] = TotalPoll( 'env' )->get( 'slug' );
			update_option( 'totalpoll_uninstall_feedback', $feedback );

			wp_remote_post( 'https://collect.totalsuite.net/uninstall', [
				'body'     => $feedback,
				'blocking' => false
			] );
		}

		wp_send_json_success();
	}
}
