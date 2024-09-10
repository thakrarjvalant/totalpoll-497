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
 * Class Analytics
 * @package TotalPoll\Admin\Ajax
 * @since   1.0.0
 */
class Analytics {
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
	 * @var array $criteria
	 */
	protected $order = [];
	/**
	 * Analytics constructor.
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

    /* ordering result array by the TotalPolls order */
    public function sortByLabel($data)
    {
        usort($data, function ($a, $b) {
            foreach ($this->order as $key => $value) {
                if ($value==$a['label']) {
                    return 0;
                    break;
                }
                if ($value==$b['label']) {
                    return 1;
                    break;
                }
            }
        });
        return $data;
    }

    /**
      * Getting eligible users vote count with option label
      * format : ['label' => 'option label','count' => 'Votes count'];
      *  @TODO: Refactor this piece of code
      * @return: multidimential array
    */
    public function getUsersVoteCountAndResolution($args)
    {
        $result = $user_votes = $user_votes_resolution = [];
        //Getting elible users count and question polls - start
        $poll_data = get_post($args['conditions']['poll_id']);
        $post_content = json_decode($poll_data->post_content, true);
        $questions = reset($post_content['questions']);
        $choice_ary = $questions['choices'];//question total choice array
        $choice_labels_ary = array_column($choice_ary, 'label');
        $post_meta_ary = get_post_meta($args['conditions']['poll_id']);
        $resolution_type = isset($post_meta_ary['resolution_type']) ? reset($post_meta_ary['resolution_type']) : '';
        //Getting users data - if respective resolution is more than zero
        $get_users_ary = ($resolution_type) ?
                          get_users(array('fields' => array( 'ID' ),'meta_key' => $resolution_type, 'meta_value' => 0,'meta_compare' => '>'))	: [];
        $total_eligible_users_count = ($get_users_ary) ? count($get_users_ary) : 0;
        $total_not_yet_votes = 0;
        $total_not_yet_votes_count = 0;
        //Getting elible users count and question polls - end
        //Getting the users resolution data -start
        foreach ($get_users_ary as $user_id) {
            $user_votes_resolution_tmp = [];
            $user_meta_ary = get_user_meta($user_id->ID);
            $user_votes_resolution_tmp['count'] = isset($user_meta_ary[$resolution_type]) ? reset($user_meta_ary[$resolution_type]) : '';
            $result = $this->log->choicePerUser('where user_id = '.$user_id->ID.' and poll_id = '.$args['conditions']['poll_id']. ' and status = "accepted"');
            if(!empty($result)) {
	            $result = reset($result);
	            $uid = reset(json_decode($result['choices']));
	            $key_label = array_search($uid, array_column($choice_ary, 'uid'));
	            if ($key_label !== false) {
	                $user_votes_resolution_tmp['label'] = ucfirst($choice_ary[$key_label]['label']);
	            } else {
	                $user_votes_resolution_tmp['label'] = 'Not Yet Voted';
	            }
	            $key = array_search($uid, array_column($user_votes_resolution, 'uid'));
	            if ($key !== false) {
	                $user_votes_resolution[$key]['count'] += $user_votes_resolution_tmp['count'];
	                $user_votes_resolution[$key]['votes'] += 1;
	            } else {
	                $user_votes_resolution_tmp['uid'] = $uid;
	                $user_votes_resolution_tmp['votes'] = 1;
	                $user_votes_resolution[] = $user_votes_resolution_tmp;
	            }
	        } else {
                $total_not_yet_votes += 1;
                $total_not_yet_votes_count +=$user_votes_resolution_tmp['count'];
			}
        }
        $result['user_votes_resolution'] = $user_votes_resolution;
        $resolution_label_ary = array_map('strtolower', array_column($user_votes_resolution, 'label'));
        //Getting the users resolution data - end

        //Getting the users vote count data and non voted resolutions labels - start
        //  $choices_votes_ary = $this->log->countVotesPerChoices($args);
        //  Refactor this block
        $choice_uid_ary = array_column($user_votes_resolution, 'uid');

        foreach ($choice_ary as $choice) {
            $key = array_search($choice['uid'], $choice_uid_ary);
            $label = ucfirst($choice['label']);
            if ($key !== false) {
                $total_eligible_users_count -= $user_votes_resolution[$key]['votes'];
                $user_votes[] = ['label' => $label,'count' => $user_votes_resolution[$key]['votes']];
            } else {
                $user_votes[] = ['label' => $label,'count' => 0];
            }
            $key = array_search(strtolower($choice['label']), $resolution_label_ary);
            $label = ucfirst($choice['label']);
            if ($key === false) {
                $result['user_votes_resolution'][] = ['label' => $label,'count' => 0];
            }
        }
        $this->order = $choice_labels_ary;
        $user_not_yet_vote = [];

        $user_not_yet_vote['label'] = 'Not Yet Voted';
		$user_not_yet_vote['uid'] = '';
	    $user_not_yet_vote['votes'] = $total_not_yet_votes;
        $user_not_yet_vote['count'] = $total_not_yet_votes_count;
	    $result['user_votes_resolution'][] = $user_not_yet_vote;
        $result['user_votes_resolution'] = $this->sortByLabel($result['user_votes_resolution']);

        //for not voted users
        $user_votes[] = ['label' => 'Not Yet Voted','count' => ($total_eligible_users_count > 0) ? $total_eligible_users_count : 0];
        $result['user_votes'] = $this->sortByLabel($user_votes);
        //Getting the users vote count data - end
        return $result;
    }

	/**
	 * Get metrics AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_analytics_metrics
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

		// $period = $this->log->countVotesPerPeriod( $args );

		// $platforms = $this->log->countVotesPerUserAgent( $args );

		unset( $args['conditions']['status'], $args['conditions']['date'], $args['conditions']['action'] );
		$poll      = $this->poll->getById( $args['conditions']['poll_id'] );
		$choicesUids = array_keys($poll->getChoices());
		if (! empty($choicesUids)):
            $args['conditions']['choice_uid'] = [ $choicesUids ];
        endif;
        $result = $this->getUsersVoteCountAndResolution($args);
        $analytics = [ 'votes' => $result['user_votes'],'resolution' => $result['user_votes_resolution']];


        /**
         * Filters the data sent to analytics browser.
         *
         * @param Model[] $polls    Array of poll models.
         * @param array   $criteria Array of criteria.
         *
         * @since 4.0.0
         * @return array
         */
        $analytics = apply_filters('totalpoll/filters/admin/analytics/metrics', $analytics, $this->criteria);

        wp_send_json($analytics);
		
	}

	/**
	 * Get polls AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_analytics_polls
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
				 * Filters the poll object sent to analytics.
				 *
				 * @param array $pollRepresentation The representation of a poll.
				 * @param Model $poll Poll model object.
				 *
				 * @return array
				 * @since 4.0.0
				 */
				return apply_filters( 'totalpoll/filters/admin/analytics/poll',
					[ 'id' => $poll->getId(), 'title' => $poll->getTitle() ], $poll, $this );
			},

			TotalPoll( 'polls.repository' )->get( $queryArgs )
		);

		/**
		 * Filters the polls list sent to analytics browser.
		 *
		 * @param Model[] $polls Array of poll models.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$polls = apply_filters( 'totalpoll/filters/admin/analytics/polls', $polls );

		wp_send_json( $polls );
	}

	/**
	 * Download AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_analytics_download
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
		do_action( 'totalpoll/actions/admin/analytics/export/columns', $export, $poll );

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
			$row = apply_filters( 'totalpoll/filters/admin/analytics/export/row', $row, $choice, $poll );

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
		

		Tracking::trackEvents("export-$format", "analytics");
		/**
		 * Filters the file writer for a specific format when exporting form entries.
		 *
		 * @param Writer $writer Writer object.
		 *
		 * @return Writer
		 * @since 4.0.0
		 */
		$writer = apply_filters( "totalpoll/filters/admin/analytics/metrics/export/writer/{$format}", $writer );

		$writer->includeColumnHeaders = true;

		$export->download( $writer, 'totalpoll-export-analytics-' . date( 'Y-m-d H:i:s' ) );

		exit;
	}
}
