<?php

namespace TotalPoll\Admin\Ajax;
! defined( 'ABSPATH' ) && exit();


use TotalPoll\Contracts\Log\Repository as LogRepository;
use TotalPoll\Contracts\Poll\Model;
use TotalPoll\Contracts\Poll\Repository as PollRepository;
use TotalPoll\Helpers\DateTime;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Export\ColumnTypes\TextColumn;
use TotalPollVendors\TotalCore\Export\Spreadsheet;
use TotalPollVendors\TotalCore\Export\Writer;
use TotalPollVendors\TotalCore\Export\Writers\CsvWriter;
use TotalPollVendors\TotalCore\Export\Writers\HTMLWriter;
use TotalPollVendors\TotalCore\Export\Writers\JsonWriter;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Insights
 * @package TotalPoll\Admin\Ajax
 * @since   1.0.0
 */
class Insights {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var LogRepository $log
	 */
	protected $log;
	/**
	 * @var PollRepository $poll
	 */
	protected $poll;
	/**
	 * @var array $criteria
	 */
	protected $criteria = [];

	/**
	 * Insights constructor.
	 *
	 * @param Request $request
	 * @param LogRepository $log
	 * @param PollRepository $poll
	 */
	public function __construct( Request $request, LogRepository $log, PollRepository $poll ) {
		$this->request = $request;
		$this->log     = $log;
		$this->poll    = $poll;

		$this->criteria = [
			'poll'   => $this->request->request( 'poll', null ),
			'from'   => $this->request->request( 'from', null ),
			'to'     => $this->request->request( 'to', null ),
			'format' => $this->request->request( 'format', null ),
		];
	}

	/**
	 * Get metrics AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_insights_metrics
	 */
	public function metrics() {
		
		$args = [ 'conditions' => [ 'status' => 'accepted', 'action' => 'vote', 'date' => [] ] ];

		if ( $this->criteria['poll'] && current_user_can( 'edit_poll', $this->criteria['poll'] ) ):
			$args['conditions']['poll_id'] = $this->criteria['poll'];
		endif;

		if ( $this->criteria['from'] && DateTime::strptime( $this->criteria['from'], '%Y-%m-%d 00:00:00' ) ):
			$args['conditions']['date'][] = [ 'operator' => '>=', 'value' => $this->criteria['from'] ];
		endif;

		if ( $this->criteria['to'] && DateTime::strptime( $this->criteria['to'], '%Y-%m-%d 00:00:00' ) ):
			$args['conditions']['date'][] = [ 'operator' => '<=', 'value' => $this->criteria['to'] ];
		endif;

		$period = $this->log->countVotesPerPeriod( $args );

		$platforms = $this->log->countVotesPerUserAgent( $args );

//		unset( $args['conditions']['status'], $args['conditions']['date'], $args['conditions']['action'] );

		$poll      = $this->poll->getById( $args['conditions']['poll_id'] );
		$pollQuestions = $poll->getQuestions();

		$questions      = [];

		foreach ( $pollQuestions as $uid => $question ) {
			$choices = [];

			foreach ($question['choices'] as $choice) {
				$choices[$choice['label']] = $this->log->countVotesPerChoice( $choice, $args );
			}

			$questions[$uid] = [
				'content'  => wp_strip_all_tags($question['content']) ?: esc_html__('Question', 'totalpoll') . ' #' . ($question['index'] + 1),
				'choices' => $choices
			];
		}

		$insights = [
			'period'    => $period,
			'questions'   => $questions,
			'platforms' => $platforms,
		];

		/**
		 * Filters the data sent to insights browser.
		 *
		 * @param Model[] $polls Array of poll models.
		 * @param array $criteria Array of criteria.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$insights = apply_filters( 'totalpoll/filters/admin/insights/metrics', $insights, $this->criteria );

		wp_send_json( $insights );
		
	}

	/**
	 * Get polls AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_insights_polls
	 */
	public function polls() {

		$queryArgs = [
			'status'  => 'any',
			'perPage' => - 1,
		];

		if ( ! current_user_can( 'edit_others_polls' ) ):
			$queryArgs['wpQuery']['author'] = get_current_user_id();
		endif;

		$polls = array_map(
			function ( $poll ) {

				/**
				 * Filters the poll object sent to insights.
				 *
				 * @param array $pollRepresentation The representation of a poll.
				 * @param Model $poll Poll model object.
				 *
				 * @return array
				 * @since 4.0.0
				 */
				return apply_filters( 'totalpoll/filters/admin/insights/poll',
					[ 'id' => $poll->getId(), 'title' => $poll->getTitle() ], $poll, $this );
			},

			TotalPoll( 'polls.repository' )->get( $queryArgs )
		);

		/**
		 * Filters the polls list sent to insights browser.
		 *
		 * @param Model[] $polls Array of poll models.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$polls = apply_filters( 'totalpoll/filters/admin/insights/polls', $polls );

		wp_send_json( $polls );
	}

	/**
	 * Download AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_insights_download
	 */
	public function download() {
		/**
		 * @var \TotalPoll\Poll\Model $poll
		 */
		$poll = $this->poll->getById( $this->criteria['poll'] );

		if ( ! $poll ) {
			wp_die( 'Poll not found' );
		}

		$export = new Spreadsheet();

		$export->addColumn( new TextColumn( 'Question' ) );
		$export->addColumn( new TextColumn( 'Choice' ) );
		$export->addColumn( new TextColumn( 'Votes' ) );
		$export->addColumn( new TextColumn( 'Votes %' ) );
		$export->addColumn( new TextColumn( 'Rank' ) );

		/**
		 * Fires after setup essential columns and before populating data. Useful for define new columns.
		 *
		 * @param Spreadsheet $export Spreadsheet object.
		 * @param \TotalPoll\Poll\Model $poll Poll object.
		 *
		 * @since 4.1.3
		 */
		do_action( 'totalpoll/actions/admin/insights/export/columns', $export, $poll );

		foreach ( $poll->getChoices() as $choice ):
			$row = [];

			$row[] = wp_strip_all_tags( $poll->getQuestion( $choice['questionUid'] )['content'] );
			$row[] = wp_strip_all_tags( $choice['label'] );
			$row[] = $choice['votes'];
			$row[] = $poll->getChoiceVotesPercentageWithLabel( $choice['uid'] );
			$row[] = $choice['rank'];
			/**
			 * Filters a row of exported entries.
			 *
			 * @param array $row Array of values.
			 * @param array $choice Choice array.
			 * @param \TotalPoll\Poll\Model $poll Poll object.
			 *
			 * @return array
			 * @since 4.1.3
			 */
			$row = apply_filters( 'totalpoll/filters/admin/insights/export/row', $row, $choice, $poll );

			$export->addRow( $row );
		endforeach;

		if ( empty( $this->criteria['format'] ) ):
			$this->criteria['format'] = 'default';
		endif;

		$format = $this->criteria['format'];

		

		
		if ( $format === 'csv' ): // CSV
			$writer = new CsvWriter();
		elseif ( $format === 'json' ): // JSON
			$writer = new JsonWriter();
		else: // Fallback to HTML
			$writer = new HTMLWriter();
		endif;
		

		Tracking::trackEvents("export-$format", "insights");
		/**
		 * Filters the file writer for a specific format when exporting form entries.
		 *
		 * @param Writer $writer Writer object.
		 *
		 * @return Writer
		 * @since 4.0.0
		 */
		$writer = apply_filters( "totalpoll/filters/admin/insights/metrics/export/writer/{$format}", $writer );

		$writer->includeColumnHeaders = true;

		$export->download( $writer, 'totalpoll-export-insignts-' . date( 'Y-m-d H:i:s' ) );

		exit;
	}
}
