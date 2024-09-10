<?php

namespace TotalPoll\Poll\Commands;
! defined( 'ABSPATH' ) && exit();


use TotalPoll\Contracts\Entry\Repository;
use TotalPoll\Contracts\Poll\Model;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Command;

class SaveEntry extends Command {
	protected $poll;
	protected $repository;

	/**
	 * SaveEntry constructor.
	 *
	 * @param Model      $poll
	 * @param Repository $repository
	 */
	public function __construct( Model $poll, Repository $repository ) {
		$this->poll       = $poll;
		$this->repository = $repository;
	}

	/**
	 * Command logic.
	 *
	 * @return mixed
	 */
	protected function handle() {
		/**
		 * Fires before saving the form entry.
		 *
		 * @param \TotalPoll\Contracts\Poll\Model $poll Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/poll/command/save-entry', $this->poll );

		$entry = $this->repository->create(
			[
				'poll_id' => $this->poll->getId(),
				'log_id'  => self::getShared( 'log.id' ),
				'fields'  => Arrays::apply($this->poll->getForm()->offsetGet( 'fields' )->toArray(), 'esc_html'),
				'details' => [],
			]
		);

		/**
		 * Fires after saving the form entry.
		 *
		 * @param \TotalPoll\Contracts\Entry\Model $entry Form entry model object.
		 * @param \TotalPoll\Contracts\Poll\Model  $poll  Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/poll/command/save-entry', $entry, $this->poll );

		if ( $entry instanceof \TotalPoll\Contracts\Entry\Model ):
			self::share( 'entry', $entry );
		endif;

		return $entry;
	}
}
