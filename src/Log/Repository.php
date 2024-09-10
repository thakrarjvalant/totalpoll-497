<?php

namespace TotalPoll\Log;
! defined( 'ABSPATH' ) && exit();


use TotalPoll\Contracts\Log\Repository as RepositoryContract;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Sql;
use WP_Error;

/**
 * Log Repository.
 * @package TotalPoll\Log
 * @since   1.0.0
 */
class Repository implements RepositoryContract {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var \wpdb $database
	 */
	protected $database;
	/**
	 * @var array $env
	 */
	protected $env;

	/**
	 * Repository constructor.
	 *
	 * @param Request $request
	 * @param \wpdb $database
	 * @param array $env
	 */
	public function __construct( Request $request, \wpdb $database, $env ) {
		$this->request  = $request;
		$this->database = $database;
		$this->env      = $env;
	}

	/**
	 * Get log entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get( $query ) {
		$args = Arrays::parse( $query, [
			'conditions'     => [],
			'page'           => 1,
			'perPage'        => 30,
			'orderBy'        => 'date',
			'orderDirection' => 'DESC',
		] );

		/**
		 * Filters the list of arguments used for get log entries query.
		 *
		 * @param array $args Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$args = apply_filters( 'totalpoll/filters/log/get/query', $args, $query );

		// Models
		$logModels = [];
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		// Order
		$order = Sql::generateOrderClause( $args['orderBy'], $args['orderDirection'] );
		// Limit clause
		$limit = $args['perPage'] === - 1 ? '' : Sql::generateLimitClause( $args['page'], $args['perPage'] );
		// Finally, our fancy SQL query
		$query = "SELECT * FROM `{$this->env['db.tables.log']}` {$where} {$order} {$limit}";

		// Get results
		$logEntries = (array) $this->database->get_results( $query, ARRAY_A );
		// Iterate and convert each row to log model
		foreach ( $logEntries as $logEntry ):
			$logModels[] = new Model( $logEntry );
		endforeach;

		/**
		 * Filters the results of log repository get query.
		 *
		 * @param \TotalPoll\Contracts\Log\Model[] $logModels Log entries models.
		 * @param array $args Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$logModels = apply_filters( 'totalpoll/filters/log/get/results', $logModels, $args, $query );

		// Return models
		return $logModels;
	}

	/**
	 * Get log entry by id.
	 *
	 * @param $logId
	 *
	 * @return \TotalPoll\Contracts\Log\Model|null
	 * @since 1.0.0
	 */
	public function getById( $logId ) {
		$result = $this->get( [ 'conditions' => [ 'id' => (int) $logId ] ] );

		return empty( $result ) ? null : $result[0];
	}

	/**
	 * Create log entry.
	 *
	 * @param $attributes
	 *
	 * @return \TotalPoll\Contracts\Log\Model|WP_Error
	 * @since 1.0.0
	 */
	public function create( $attributes ) {

		$attributes = Arrays::parse(
			$attributes,
			[
				'date'      => TotalPoll( 'datetime' ),
				'ip'        => $this->request->ip(),
				'useragent' => $this->request->userAgent(),
				'user_id'   => get_current_user_id(),
				'choices'   => [],
				'details'   => [],
			]
		);

		/**
		 * Filters the attributes of an log entry model used for insertion.
		 *
		 * @param array $attributes Entry attributes.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$attributes = apply_filters( 'totalpoll/filters/log/insert/attributes', $attributes );

		if ( empty( $attributes['poll_id'] ) || empty( $attributes['action'] ) ):
			return new WP_Error( 'missing_fields', esc_html__( 'poll_id and action are required' ) );
		endif;


		$logModelAttributes = [
			'date'      => $attributes['date']->format( 'Y-m-d H:i:s' ),
			'ip'        => (string) $attributes['ip'],
			'useragent' => (string) $attributes['useragent'],
			'user_id'   => (int) $attributes['user_id'],
			'poll_id'   => (int) $attributes['poll_id'],
			'action'    => (string) $attributes['action'],
			'status'    => (string) $attributes['status'],
			'choices'   => json_encode( (array) $attributes['choices'] ),
			'details'   => json_encode( (array) $attributes['details'] ),
		];

		if ( ! TotalPoll()->option( 'advanced.disableLog' ) ) {
			$inserted = $this->database->insert( $this->env['db.tables.log'], $logModelAttributes );

			if ( ! $inserted ):
				return new WP_Error( 'insert_fail', esc_html__( 'Unable to insert the entry.', 'totalpoll' ), $logModelAttributes );
			endif;

			$logModelAttributes['id'] = $this->database->insert_id;
		} else {
			$logModelAttributes['id'] = 0;
		}

		/**
		 * Filters the log entry model attributes after insertion.
		 *
		 * @param array $entryModel Log entry attributes.
		 * @param array $attributes Original insertion attributes.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$logModelAttributes = apply_filters( 'totalpoll/filters/log/insert/model', $logModelAttributes );

		return new Model( $logModelAttributes );
	}

	/**
	 * @param int $id
	 * @param array $attributes
	 *
	 * @return int|WP_Error
	 */
	public function update( $id, $attributes ) {
		$updated = $this->database->update( $this->env['db.tables.log'], $attributes, [ 'id' => $id ] );

		if ( ! $updated ) :
			return new WP_Error( 'update_fail', esc_html__( 'Unable to update the entry.', 'totalpoll' ) );
		endif;

		return $id;
	}

	/**
	 * Delete log entries.
	 *
	 * @param $query array
	 *
	 * @return bool|WP_Error
	 * @since 1.0.0
	 */

	public function delete( $query ) {
		$where = Sql::generateWhereClause( $query );

		if ( empty( $where ) ):
			return new WP_Error( 'no_conditions', esc_html__( 'No conditions were specified', 'totalpoll' ) );
		endif;

		$query = "DELETE FROM `{$this->env['db.tables.log']}` {$where}";

		return (bool) $this->database->query( $query );

	}

	/**
	 * Purge log entries.
	 *
	 * @param null $poll
	 *
	 * @return bool|WP_Error
	 * @since 1.0.0
	 */
	public function purge( $poll = null ) {

		if ( $poll ) {
			$query = "DELETE FROM `{$this->env['db.tables.log']}` WHERE poll_id = %d";
		} else {
			$query = "DELETE FROM `{$this->env['db.tables.log']}`";
		}

		$query = $this->database->prepare( $query, $poll );

		return $this->database->query( $query ) !== false;

	}

	/**
	 * Count log entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function count( $query ) {
		$args = Arrays::parse( $query, [ 'conditions' => [], ] );

		/**
		 * Filters the list of arguments used for count log entries query.
		 *
		 * @param array $args Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$args = apply_filters( 'totalpoll/filters/log/count/query', $args );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		// Finally, our fancy SQL query
		$query = "SELECT COUNT(*) FROM `{$this->env['db.tables.log']}` {$where}";

		// Get count
		return (int) $this->database->get_var( $query );
	}

	/**
	 * @param $query
	 *
	 * @TODO: Refactor this piece of code
	 * @return array
	 */
	public function countVotesPerPeriod( $query ) {
		$args = Arrays::parse( $query, [ 'conditions' => [], ] );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		// Finally, our fancy SQL query
		$query = "SELECT SUM(`votes`) AS `votes`, `period` FROM (SELECT 1 + ROUND ( IFNULL( LENGTH(`choices`) - LENGTH( REPLACE ( `choices`, ',', '') ), 0 ) / LENGTH(',') ) AS `votes`, DATE_FORMAT(`date`, '%Y-%m-%d') AS `period` FROM `{$this->env['db.tables.log']}` {$where}) `periods_votes` GROUP BY `period` ORDER BY period ASC";

		// Get count
		return $this->database->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $query
	 *
	 * @TODO: Refactor this piece of code
	 * @return array
	 */
	public function countVotesPerChoice( $choice, $query ) {
		$args = Arrays::parse( $query, [ 'conditions' => [], ] );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		$where = ( $where ? "$where AND" : "" ) . " choices LIKE '%{$choice['uid']}%' ";

		// Finally, our fancy SQL query
		$query = "SELECT COUNT(*) AS `votes` FROM `{$this->env['db.tables.log']}` {$where}";

		// Get count
		return (int) current( $this->database->get_row( $query, ARRAY_A ) );
	}

	/**
	 * @param $query
	 *
	 * @TODO: Refactor this piece of code
	 * @return array
	 */
	public function countVotesPerChoices( $query ) {
		$args = Arrays::parse( $query, [ 'conditions' => [], ] );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		$query = "SELECT `choice_uid`, `votes` FROM `{$this->env['db.tables.votes']}` {$where}";

		// Get count
		return $this->database->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $query
	 *
	 * @TODO: Refactor this piece of code
	 * @return array
	 */
	public function countVotesPerUserAgent( $query ) {
		$args = Arrays::parse( $query, [ 'conditions' => [], ] );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		// Finally, our fancy SQL query
		$query = "SELECT SUM(`votes`) AS `votes`, `browser`, `os` FROM (SELECT 1 + ROUND ( IFNULL( LENGTH(`choices`) - LENGTH( REPLACE ( `choices`, ',', '') ), 0) / LENGTH(',') ) AS `votes`, CASE WHEN `useragent` LIKE '%Mac%OS%' THEN 'Mac OS X' WHEN `useragent` LIKE '%iPad%' THEN 'iPad' WHEN `useragent` LIKE '%iPod%' THEN 'iPod' WHEN `useragent` LIKE '%iPhone%' THEN 'iPhone' WHEN `useragent` LIKE '%imac%' THEN 'mac' WHEN `useragent` LIKE '%android%' THEN 'android' WHEN `useragent` LIKE '%linux%' THEN 'linux' WHEN `useragent` LIKE '%Nokia%' THEN 'Nokia' WHEN `useragent` LIKE '%BlackBerry%' THEN 'BlackBerry' WHEN `useragent` LIKE '%win%' THEN CASE WHEN `useragent` LIKE '%NT 10%' THEN 'Windows 10' WHEN `useragent` LIKE '%NT 6.3%' THEN 'Windows 8.1' WHEN `useragent` LIKE '%NT 6.2%' THEN 'Windows 8' WHEN `useragent` LIKE '%NT 6.1%' THEN 'Windows 7' WHEN `useragent` LIKE '%NT 6.0%' THEN 'Windows Vista' WHEN `useragent` LIKE '%NT 5.1%' THEN 'Windows XP' WHEN `useragent` LIKE '%NT 5.0%' THEN 'Windows 2000' ELSE 'Windows' END WHEN `useragent` LIKE '%FreeBSD%' THEN 'FreeBSD' WHEN `useragent` LIKE '%OpenBSD%' THEN 'OpenBSD' WHEN `useragent` LIKE '%NetBSD%' THEN 'NetBSD' WHEN `useragent` LIKE '%OpenSolaris%' THEN 'OpenSolaris' WHEN `useragent` LIKE '%SunOS%' THEN 'SunOS' WHEN `useragent` LIKE '%OS/2%' THEN 'OS/2' WHEN `useragent` LIKE '%BeOS%' THEN 'BeOS' ELSE 'Unknown' END AS `os`, CASE WHEN `useragent` LIKE '%edge%' THEN 'Edge' WHEN `useragent` LIKE '%MSIE%' THEN 'Internet Explorer' WHEN `useragent` LIKE '%Firefox%' THEN 'Mozilla Firefox' WHEN `useragent` LIKE '%Chrome%' THEN 'Google Chrome' WHEN `useragent` LIKE '%Safari%' THEN 'Apple Safari' WHEN `useragent` LIKE '%Opera%' THEN 'Opera' WHEN `useragent` LIKE '%Outlook%' THEN 'Outlook' ELSE 'Unknown' END AS `browser` FROM `{$this->env['db.tables.log']}` {$where}) `browser_os_votes` GROUP BY `browser`, `os`";

		// Get count
		return $this->database->get_results( $query, ARRAY_A );
	}

	/**
	 * Anonymize log entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function anonymize( $query ) {
		$args = Arrays::parse( $query, [
			'conditions' => [],
		] );

		/**
		 * Filters the list of arguments used for anonymize log entries query.
		 *
		 * @param array $args Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$args = apply_filters( 'totalpoll/filters/log/anonymize/query', $args, $query );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );

		if ( empty( $where ) ):
			return new WP_Error( 'no_conditions', esc_html__( 'No conditions were specified', 'totalpoll' ) );
		endif;

		// Finally, our fancy SQL query
		$query = "UPDATE `{$this->env['db.tables.log']}` SET `user_id` = 0, `ip` = '', `useragent` = '', `details` = '{\"anonymized\":true}' {$where}";

		// Get results
		return (bool) $this->database->query( $query );

	}

	/**
	 * Count orphaned log entries.
	 *
	 * @return int
	 */
	public function countOrphaned() {
		$query = "SELECT COUNT(*) FROM `{$this->env['db.tables.log']}` LEFT JOIN `{$this->database->posts}` ON `{$this->env['db.tables.log']}`.`poll_id` = `{$this->database->posts}`.`ID` WHERE `{$this->database->posts}`.`ID` IS NULL";

		return $this->database->get_var( $query );
	}

	/**
	 * Delete orphaned log entries.
	 *
	 * @return int
	 */
	public function deleteOrphaned() {
		$query = "DELETE log FROM `{$this->env['db.tables.log']}` log LEFT JOIN `{$this->database->posts}` posts ON `log`.`poll_id` = `posts`.`ID` WHERE `posts`.`ID` IS NULL";

		return (bool) $this->database->query( $query );
	}
    /**
     * Export User Data
     *
     * @return csv
    */
    public function exportUserData()
    {
        global $wpdb;
        $usermeta = $wpdb->usermeta;
        $postmeta = $wpdb->postmeta;
        $posts = $wpdb->posts;
        $query = "SELECT
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'blueprintRef' limit 1) as 'Blueprint Ref',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'role' limit 1) as 'Role',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'last_name' limit 1) as 'Last Name',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'first_name' limit 1) as 'First Name',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'other_names' limit 1) as 'Preferred Name',
            u.user_email AS email,
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'phone' limit 1) as 'phone',
            u.user_email AS email,
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'address1' limit 1) as 'Address1',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'address2' limit 1) as 'Address2',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'address3' limit 1) as 'Address3',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'address4' limit 1) as 'Address4',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'address5' limit 1) as 'Address5',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'address6' limit 1) as 'Address6',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'country' limit 1) as 'Country',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'DateOfBirth' limit 1) as 'Date of Birth',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'NINumber' limit 1) as 'National Insurance Number',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'workdayNumber' limit 1) as 'Workday Employee Number',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'share_A_ordinary' limit 1) as 'Share A Ordinary',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'share_A_ordinary_converted' limit 1) as 'Share A Ordinary Converted',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'share_D_ordinary' limit 1) as 'Share D Ordinary',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'share_E_ordinary' limit 1) as 'Share E Ordinary',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'share_F_ordinary' limit 1) as 'Share F Ordinary',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'eligible_A_shares' limit 1) as 'Eligible A Shares',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res1_holding' limit 1) as 'Res1 Holding',
            
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res1_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 1 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res2_holding' limit 1) as 'Res2 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id  left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res2_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 2 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res3_holding' limit 1) as 'Res3 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res3_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 3 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res4_holding' limit 1) as 'Res4 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res4_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 4 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res5_holding' limit 1) as 'Res5 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res5_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 5 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res6_holding' limit 1) as 'Res6 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res6_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 6 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res7_holding' limit 1) as 'Res7 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res7_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 7 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res8_holding' limit 1) as 'Res8 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res8_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 8 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res9_holding' limit 1) as 'Res9 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res9_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 9 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'res10_holding' limit 1) as 'Res10 Holding',
            (select JSON_UNQUOTE(JSON_EXTRACT(details,'$.choices[0]')) from `{$postmeta}` pm left join `wp_totalpoll_log` tpl on tpl.poll_id = pm.post_id left join `{$posts}` p on p.ID = tpl.poll_id where p.post_status <> 'trash' and pm.meta_value = 'res10_holding' and pm.meta_key='resolution_type' and tpl.action='vote' and status='accepted' and tpl.user_id = u.id limit 1) as 'Resolution 10 Vote',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'maxsell_A_ordinary' limit 1) as 'A Ordinary Max Sell',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'sell_A_ordinary' limit 1) as 'A Ordinary Sell',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'maxsell_A_ordinary_converted' limit 1) as 'A Ordinary (Converted) Max Sell',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'sell_A_ordinary_converted' limit 1) as 'A Ordinary (Converted) Sell',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'bankacc' limit 1) as 'Bank Account Details',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'buy_A_ordinary' limit 1) as 'A Ordinary Buy',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'shares_Value' limit 1) as 'Shares Value',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'stamp_Duty' limit 1) as 'Stamp Duty',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'total_Due' limit 1) as 'Total Due',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'proceeds_Tranche1' limit 1) as 'Proceeds Tranche 1',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'proceeds_Tranche2' limit 1) as 'Proceeds Tranche 2',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'proceeds_Tranche3' limit 1) as 'Proceeds Tranche 3',
            (select meta_value from `{$usermeta}` where user_id = u.id and meta_key = 'total_Proceeds' limit 1) as 'Total Proceeds'
        FROM wp_users u";

        $data_ary = $this->database->get_results($query, ARRAY_A);

        if (count($data_ary) > 0) {
            $i = 0;
            $csv_data = '';
            foreach ($data_ary as $data) {
                if ($i == 0) {
                    $csv_data .= implode(',', array_keys($data))."\n";
                }
                $data = array_map(function ($v) {
                    return '"' . str_replace('"', '""', $v) . '"';
                }, $data);


                $csv_data .= implode(',', $data)."\n";
                $i++;
            }
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="users_resolution.csv"');
            echo $csv_data;
            wp_die();

        }
    }
    /**
      * Getting use choices from log table
      * @return array
    */
    public function choicePerUser($where)
    {
        $query = "SELECT `choices` FROM `wp_totalpoll_log` {$where}";
        return $this->database->get_results($query, ARRAY_A);
    }
}