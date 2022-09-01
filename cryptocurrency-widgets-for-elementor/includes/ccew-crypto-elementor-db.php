<?php
/**
 * This file is responsible for all database realted functionality.
 */
class ccew_database {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->base_prefix . 'ccew_coins';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_columns() {
		 return array(
			 'id'                 => '%d',
			 'coin_id'            => '%s',
			 'rank'               => '%d',
			 'name'               => '%s',
			 'symbol'             => '%s',
			 'price'              => '%f',
			 'percent_change_1h'  => '%f',
			 'percent_change_24h' => '%f',
			 'percent_change_7d'  => '%f',
			 'percent_change_30d' => '%f',
			 'low_24h'            => '%f',
			 'high_24h'           => '%f',
			 'market_cap'         => '%f',
			 'total_volume'       => '%f',
			 'total_supply'       => '%f',
			 'circulating_supply' => '%d',
			 'logo'               => '%s',
			 '7d_chart'           => '%s',
			 'coin_last_updated'  => '%s',
			 'extra_data'         => '%s',

		 );
	}

	function ccew_insert( $coins_data ) {

		if ( is_array( $coins_data ) ) {

			return $this->wp_insert_rows( $coins_data, $this->table_name, true, 'coin_id' );
		}
	}
	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_column_defaults() {
		 return array(
			 'coin_id'            => '',
			 'rank'               => '',
			 'name'               => '',
			 'symbol'             => '',
			 'price'              => '',
			 'percent_change_1h'  => '',
			 'percent_change_24h' => '',
			 'percent_change_7d'  => '',
			 'percent_change_30d' => '',
			 'low_24h'            => '',
			 'high_24h'           => '',
			 'market_cap'         => '',
			 'total_volume'       => '',
			 'total_supply'       => '',
			 'circulating_supply' => '',
			 'logo'               => '',
			 '7d_chart'           => '',
			 'coin_last_update'   => '',
			 'extra_data'         => '',
			 'last_updated'       => gmdate( 'Y-m-d H:i:s' ),
		 );
	}

	public function coin_exists_by_id( $coin_id ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->table_name WHERE coin_id ='%s'", $coin_id ) );
		if ( $count == 1 ) {
			return true;
		} else {
			return false;
		}

	}

	public function check_coin_latest_update( $coin_id ) {
		global $wpdb;
		$coin_update_value  = $wpdb->get_var( $wpdb->prepare( "SELECT coin_last_update FROM $this->table_name WHERE coin_id ='%s'", $coin_id ) );
		$coin_latest_update = trim( $coin_update_value, 'TZ' );
		$currentdate        = gmdate( 'Y-m-d h:i:s' );
		$datetime1          = new DateTime( $currentdate );
		$datetime2          = new DateTime( $coin_update_value );
		$interval           = $datetime1->diff( $datetime2 );
		if ( $interval->y == 0 && $interval->m == 0 && $interval->h == 0 && $interval->i < 10 ) {
			return true;
		} else {
			return false;

		}

	}

	public function get_coin_logo( $coin_id = null ) {
		if ( ! empty( $coin_id ) ) {
			global $wpdb;
			$logo = $wpdb->get_var( $wpdb->prepare( 'SELECT logo FROM ' . $this->table_name . ' WHERE coin_id=%s', $coin_id ) );

			return $logo;
		} else {
			throw new Exception( "one argument 'coin_id' expected. Null given," );
		}
	}


	
	/**
	 * Retrieve orders from the database
	 *
	 * @access  public
	 * @since   1.0
	 * @param   array $args
	 * @param   bool  $count  Return only the total number of results found (optional)
	 */
	public $count = 1;
	public function get_coins( $args = array(), $count = false ) {

		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'id'      => '',
			'coin_id' => '',
			'name'    => '',
			'status'  => '',
			'orderby' => 'id',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 1000;
		}

		$where = '';

		// specific referrals
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$order_ids = implode( ',', $args['id'] );
			} else {
				$order_ids = intval( $args['id'] );
			}

			$where .= "WHERE `id` IN( {$order_ids} ) ";

		}

		if ( ! empty( $args['coin_id'] ) ) {

			if ( empty( $where ) ) {
				$where .= ' WHERE';
			} else {
				$where .= ' AND';
			}

			if ( is_array( $args['coin_id'] ) ) {
				$where .= " `coin_id` IN('" . implode( "','", $args['coin_id'] ) . "') ";
			} else {
				$where .= " `coin_id` = '" . $args['coin_id'] . "' ";
			}
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

		if ( 'total' === $args['orderby'] ) {
			$args['orderby'] = 'total+0';
		} elseif ( 'subtotal' === $args['orderby'] ) {
			$args['orderby'] = 'subtotal+0';
		}

		$cache_key = ( true === $count ) ? filter_var( md5( 'ccew_coins_count' . serialize( $args ) ), FILTER_SANITIZE_STRING ) : filter_var( md5( 'ccew_coins_' . serialize( $args ) ), FILTER_SANITIZE_STRING );

		$results = wp_cache_get( $cache_key, 'coins' );

		if ( false === $results ) {

			if ( true === $count ) {

				$results = absint( $wpdb->get_var( "SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};" ) );

			} else {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint( $args['offset'] ),
						absint( $args['number'] )
					)
				);

			}

			wp_cache_set( $cache_key, $results, 'coins', 3600 );

		}

		return $results;

	}


	public function get_coins_listdata( $args = array(), $count = false ) {
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'coin_id' => '',
			'name'    => '',
			'status'  => '',
			'email'   => '',
			'orderby' => 'id',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// specific referrals
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$order_ids = implode( ',', $args['id'] );
			} else {
				$order_ids = intval( $args['id'] );
			}

			$where .= "WHERE `id` IN( {$order_ids} ) ";

		}

		if ( ! empty( $args['coin_id'] ) ) {

			if ( empty( $where ) ) {
				$where .= ' WHERE';
			} else {
				$where .= ' AND';
			}

			if ( is_array( $args['coin_id'] ) ) {
				$where .= " `coin_id` IN('" . implode( "','", $args['coin_id'] ) . "') ";
			} else {
				$where .= " `coin_id` = '" . $args['coin_id'] . "' ";
			}
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

		if ( 'total' === $args['orderby'] ) {
			$args['orderby'] = 'total+0';
		} elseif ( 'subtotal' === $args['orderby'] ) {
			$args['orderby'] = 'subtotal+0';
		}

		$cache_key = ( true === $count ) ? filter_var( md5( 'ccew_coins_list_count' . serialize( $args ) ), FILTER_SANITIZE_STRING ) : filter_var( md5( 'ccew_coins_list_' . serialize( $args ) ), FILTER_SANITIZE_STRING );

		$results = wp_cache_get( $cache_key, 'coins' );

		if ( false === $results ) {

			if ( true === $count ) {

				$results = absint( $wpdb->get_var( "SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};" ) );

			} else {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT name,price,symbol,coin_id FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint( $args['offset'] ),
						absint( $args['number'] )
					)
				);

			}

			wp_cache_set( $cache_key, $results, 'coins', 3600 );

		}

		return $results;

	}




	/**
	 *  A method for inserting multiple rows into the specified table
	 *  Updated to include the ability to Update existing rows by primary key
	 *
	 *  Usage Example for insert:
	 *
	 *  $insert_arrays = array();
	 *  foreach($assets as $asset) {
	 *  $time = current_time( 'mysql' );
	 *  $insert_arrays[] = array(
	 *  'type' => "multiple_row_insert",
	 *  'status' => 1,
	 *  'name'=>$asset,
	 *  'added_date' => $time,
	 *  'last_update' => $time);
	 *
	 *  }
	 *
	 *
	 *  wp_insert_rows($insert_arrays, $wpdb->tablename);
	 *
	 *  Usage Example for update:
	 *
	 *  wp_insert_rows($insert_arrays, $wpdb->tablename, true, "primary_column");
	 *
	 * @param array   $row_arrays
	 * @param string  $wp_table_name
	 * @param boolean $update
	 * @param string  $primary_key
	 * @return false|int
	 */
	function wp_insert_rows( $row_arrays, $wp_table_name, $update = false, $primary_key = null ) {
		global $wpdb;
		$wp_table_name = esc_sql( $wp_table_name );
		// Setup arrays for Actual Values, and Placeholders
		$values        = array();
		$place_holders = array();
		$query         = '';
		$query_columns = '';

		$floatCols = array( 'price', 'low_24h', 'high_24h', 'percent_change_1h', 'percent_change_24h', 'percent_change_7d', 'percent_change_30d', 'market_cap', 'total_volume', 'total_supply', 'circulating_supply' );
		$query    .= "INSERT INTO `{$wp_table_name}` (";
		foreach ( $row_arrays as $count => $row_array ) {
			foreach ( $row_array as $key => $value ) {
				if ( $count == 0 ) {
					if ( $query_columns ) {
						$query_columns .= ', `' . $key . '`';
					} else {
						$query_columns .= '`' . $key . '`';
					}
				}

				$values[] = $value;

				$symbol = '%s';
				if ( is_numeric( $value ) ) {
						$symbol = '%d';
				}

				if ( in_array( $key, $floatCols ) ) {
					$symbol = '%f';
				}
				if ( isset( $place_holders[ $count ] ) ) {
					$place_holders[ $count ] .= ", '$symbol'";
				} else {
					$place_holders[ $count ] = "( '$symbol'";
				}
			}
			// mind closing the GAP
			$place_holders[ $count ] .= ')';
		}

		$query .= " $query_columns ) VALUES ";

		$query .= implode( ', ', $place_holders );

		if ( $update ) {
			$update = " ON DUPLICATE KEY UPDATE `$primary_key`=VALUES( `$primary_key` ),";
			$cnt    = 0;
			foreach ( $row_arrays[0] as $key => $value ) {
				if ( $cnt == 0 ) {
					$update .= "`$key`=VALUES(`$key`)";
					$cnt     = 1;
				} else {
					$update .= ", `$key`=VALUES(`$key`)";
				}
			}
			$query .= $update;
		}

		$sql = $wpdb->prepare( $query, $values );

		if ( $wpdb->query( $sql ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return the number of results found for a given query
	 *
	 * @param  array $args
	 * @return int
	 */
	public function count( $args = array() ) {
		return $this->get_coins( $args, true );
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$sql = 'CREATE TABLE ' . $this->table_name . ' (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`coin_id` varchar(200) NOT NULL UNIQUE,
			`rank` int(9),
			`name` varchar(250) NOT NULL,
			`symbol` varchar(100) NOT NULL,
			`price` decimal(20,8),
			`percent_change_1h` decimal(7,4) ,
			`percent_change_24h` decimal(7,4) ,
			`percent_change_7d` decimal(7,4) ,
			`percent_change_30d` decimal(7,4) ,
			`low_24h` decimal(20,8),
			`high_24h` decimal(20,8),
			`market_cap` decimal(24,2),
			`total_volume` decimal(24,2) ,
			`total_supply` decimal(24,2) ,
			`circulating_supply` varchar(250),
			`logo` varchar(250),
			`7d_chart` longtext,
			`coin_last_update` varchar(250),
			`extra_data` varchar(250),
			`last_updated` TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
			PRIMARY KEY (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;';

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Drop database table
	 */
	public function drop_table() {
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->table_name );

	}
}
