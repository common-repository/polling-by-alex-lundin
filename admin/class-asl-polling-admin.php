<?php /** @noinspection ALL */

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Polling
 * @subpackage Asl_Polling/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Asl_Polling
 * @subpackage Asl_Polling/admin
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class Asl_Polling_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Custom Post Type Name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $cpt_name .
	 */
	private $cpt_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->cpt_name    = 'asl-poll';

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_data_polling_scripts' ) );
	}

	/**
	 * Register form post types
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$args = array(
			'label'               => __( 'Polling', 'asl-polling' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'labels'              => array(
				'name'               => __( 'Polling', 'asl-polling' ),
				'singular_name'      => __( 'Poll', 'asl-polling' ),
				'menu_name'          => __( 'Polling', 'asl-polling' ),
				'add_new'            => __( 'Add Poll', 'asl-polling' ),
				'add_new_item'       => __( 'Add New Poll', 'asl-polling' ),
				'edit'               => __( 'Edit', 'asl-polling' ),
				'edit_item'          => __( 'Edit Poll', 'asl-polling' ),
				'new_item'           => __( 'New Poll', 'asl-polling' ),
				'view'               => __( 'View Poll', 'asl-polling' ),
				'view_item'          => __( 'View Poll', 'asl-polling' ),
				'search_items'       => __( 'Search Poll', 'asl-polling' ),
				'not_found'          => __( 'No Poll Found', 'asl-polling' ),
				'not_found_in_trash' => __( 'No Poll Found in Trash', 'asl-polling' ),
				'parent'             => __( 'Parent Poll', 'asl-polling' ),
			),
		);
		register_post_type( $this->cpt_name, $args );
	}

	public function add_menu() {
		global $submenu;
		$capability = asl_polling_admin_role();

		if ( ! $capability ) {
			return;
		}

		$menuName = __( 'Polling', 'asl-polling' );

		add_menu_page(
			$menuName,
			$menuName,
			$capability,
			'asl_polling',
			array( $this, 'main_page' ),
			'dashicons-format-status',
			20
		);

		$submenu['asl_polling']['all_polls'] = array(
			__( 'Polls', 'asl-polling' ),
			$capability,
			'admin.php?page=asl_polling#/home',
		);

		$submenu['asl_polling']['import'] = array(
			__( 'Import', 'asl-polling' ),
			$capability,
			'admin.php?page=asl_polling#/tools',
		);

		$submenu['asl_polling']['tools'] = array(
			__( 'Tools', 'asl-polling' ),
			$capability,
			'admin.php?page=asl_polling#/tools',
		);

		aslPollsAdminPrintStyles();

		$submenu['asl_polling']['help'] = array(
			__( 'Help', 'asl-polling' ),
			$capability,
			'admin.php?page=asl_polling#/help',
		);
	}

	public function main_page() {
		include( plugin_dir_path( __FILE__ ) . 'partials/asl-polling-admin-display.php' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/prod/css/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-manifest', plugin_dir_url( __FILE__ ) . 'assets/prod/js/manifest.js', array(), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-vendor', plugin_dir_url( __FILE__ ) . 'assets/prod/js/vendor.js', array( $this->plugin_name . '-manifest' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-app', plugin_dir_url( __FILE__ ) . 'assets/prod/js/admin.js', array( $this->plugin_name . '-vendor' ), $this->version, true );
		wp_set_script_translations( $this->plugin_name . '-app', 'asl-polling', plugin_dir_path( __FILE__ ) . '/languages' );

		if ( current_user_can( 'manage_options' ) ) {
			$isAdmin = 'yes';
		} else {
			$isAdmin = 'no';
		}

		wp_localize_script( $this->plugin_name . '-manifest', 'asl_polling_admin', array(
			'asl_rest_uri' => get_rest_url(),
			'nonce'        => wp_create_nonce( 'wp_rest' )
		) );

	}

	public function register_poll_routes() {
		register_rest_route( 'asl-polls/v1', '/polls', [
			[
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_polls' ),
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_create_poll' ),
				'permission_callbacl' => function () {
					return current_user_can( 'edit_posts' );
				}
			]
		] );

		register_rest_route( 'asl-polls/v1', '/polls/(?P<id>\d+)', [
			[
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_poll' ),
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'rest_delete_poll' ),
				'permission_callbacl' => function () {
					return current_user_can( 'edit_posts' );
				}
			],
			[
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_edit_poll' ),
				'permission_callbacl' => function () {
					return current_user_can( 'edit_posts' );
				}
			]
		] );
	}

	public function rest_get_polls() {
		global $wpdb;
		$dbName   = $wpdb->prefix . asl_polling_db_table_name();
		$headPost = $wpdb->get_results( "SELECT ID, post_title, post_content FROM $wpdb->posts WHERE `post_type` = '$this->cpt_name'" );

		if ( ! empty( $headPost ) ) {
			$contentPost = $wpdb->get_results( "SELECT id, item_id, moderate, rating, value, settings FROM $dbName" );
			foreach ( $headPost as $poll ) {
				$response[] = [
					'id'          => $poll->ID,
					'name'        => $poll->post_title,
					'description' => $poll->post_content,
					'pluses'      => [],
					'minuses'     => []
				];
			}
			for ( $i = 0; $i < (int) count( $contentPost ); $i ++ ) {
				for ( $k = 0; $k < (int) count( $response ); $k ++ ) {
					if ( $response[ $k ]['id'] === $contentPost[ $i ]->item_id ):
						if ( $contentPost[ $i ]->settings === 'headMinus' ) {
							$response[ $k ]['headMinus'] = $contentPost[ $i ]->value;
						}

						if ( $contentPost[ $i ]->settings === 'headPlus' ) {
							$response[ $k ]['headPlus'] = $contentPost[ $i ]->value;
						}

						if ( $contentPost[ $i ]->settings === 'plus' ):
							$response[ $k ]['pluses'][] = [
								'position' => $contentPost[ $i ]->settings,
								'rating'   => $contentPost[ $i ]->rating,
								'text'     => $contentPost[ $i ]->value,
								'publish'  => $contentPost[ $i ]->moderate === '1',
								'id'       => $contentPost[ $i ]->id,
							];
						endif;

						if ( $contentPost[ $i ]->settings === 'minus' ):
							$response[ $k ]['minuses'][] = [
								'position' => $contentPost[ $i ]->settings,
								'rating'   => $contentPost[ $i ]->rating,
								'text'     => $contentPost[ $i ]->value,
								'publish'  => $contentPost[ $i ]->moderate === '1',
								'id'       => $contentPost[ $i ]->id
							];
						endif;
					endif;
				}
			}
			wp_send_json( $response, 200 );
		} else {
			wp_send_json( [], 200 );
		}
	}

	public function rest_create_poll( WP_REST_Request $request ) {
		global $wpdb;
		$dbName = $wpdb->prefix . asl_polling_db_table_name();
		$data   = json_decode( stripslashes( $request->get_param( 'data' ) ), true );

		$post_data = [
			'post_title'   => sanitize_text_field( $data['name'] ),
			'post_content' => sanitize_text_field( $data['description'] ),
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_type'    => $this->cpt_name
		];

		$post_id = wp_insert_post( $post_data );

		$wpdb->insert(
			$wpdb->prefix . asl_polling_db_table_name(),
			[
				'item_id'    => $post_id,
				'settings'   => 'headPlus',
				'value'      => $data['headPlus'],
				'created_at' => date( "Y-m-d H:i:s" ),
				'updated_at' => date( "Y-m-d H:i:s" ),
			]
		);

		$wpdb->insert(
			$wpdb->prefix . asl_polling_db_table_name(),
			[
				'item_id'    => $post_id,
				'settings'   => 'headMinus',
				'value'      => $data['headMinus'],
				'created_at' => date( "Y-m-d H:i:s" ),
				'updated_at' => date( "Y-m-d H:i:s" ),
			]
		);

		if ( ! empty( $data['pluses'] ) ) {
			foreach ( $data['pluses'] as $item ) {
				$wpdb->insert(
					$wpdb->prefix . asl_polling_db_table_name(),
					[
						'item_id'    => $post_id,
						'value'      => $item['text'],
						'settings'   => 'plus',
						'rating'     => $item['rating'],
						'moderate'   => $item['publish'],
						'created_at' => date( "Y-m-d H:i:s" ),
						'updated_at' => date( "Y-m-d H:i:s" ),
					]
				);
			}
		} else {
			$wpdb->insert(
				$wpdb->prefix . asl_polling_db_table_name(), [
				'pluses' => []
			] );
		}

		if ( ! empty( $data['minuses'] ) ) {
			foreach ( $data['minuses'] as $item ) {
				$wpdb->insert(
					$wpdb->prefix . asl_polling_db_table_name(),
					[
						'item_id'    => $post_id,
						'value'      => $item['text'],
						'settings'   => 'minus',
						'rating'     => $item['rating'],
						'moderate'   => $item['publish'],
						'created_at' => date( "Y-m-d H:i:s" ),
						'updated_at' => date( "Y-m-d H:i:s" ),
					]
				);
			}
		} else {
			$wpdb->insert(
				$wpdb->prefix . asl_polling_db_table_name(), [
				'minuses' => []
			] );
		}


		$headPost    = $wpdb->get_results( "SELECT post_title, post_content FROM $wpdb->posts WHERE ID = $post_id" );
		$postContent = $wpdb->get_results( "SELECT id, moderate, rating, value, settings FROM $dbName WHERE item_id = $post_id" );

		$resPlus  = [];
		$resMinus = [];
		$response = [
			'id'                 => $post_id,
			'name'               => $headPost[0]->post_title,
			'description'        => $headPost[0]->post_content,
			'headPlus'           => $postContent[0]->value,
			'headMinus'          => $postContent[1]->value,
			'pluses'             => [],
			'minuses'            => [],
			'moderateMinusCount' => 0,
			'moderatePlusCount'  => 0
		];

		foreach ( $postContent as $key => $item ) {
			if ( $item->settings === 'plus' ):
				$resPlus[ $key ]['position'] = $item->settings;
				$resPlus[ $key ]['rating']   = $item->rating;
				$resPlus[ $key ]['text']     = $item->value;
				$resPlus[ $key ]['publish']  = (boolean) $item->moderate;

			endif;

			if ( $item->settings === 'minus' ):
				$resMinus[ $key ]['position'] = $item->settings;
				$resMinus[ $key ]['rating']   = $item->rating;
				$resMinus[ $key ]['text']     = $item->value;
				$resMinus[ $key ]['publish']  = (boolean) $item->moderate;
			endif;
		}

		$response['pluses'][]  = $resPlus;
		$response['minuses'][] = $resMinus;

		wp_send_json( $response );
	}

	public function rest_delete_poll( WP_REST_Request $request ) {
		global $wpdb;
		$dbName = $wpdb->prefix . asl_polling_db_table_name();
		$id     = $request->get_param( 'id' );
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $dbName WHERE item_id = '%d'",
				$id
			)
		);

		$del = wp_delete_post( $id );
		wp_send_json( $del );
	}

	public function moderate_count() {
		global $menu;
		global $wpdb;
		$dbName = $wpdb->prefix . asl_polling_db_table_name();

		$res   = $wpdb->get_results( "SELECT `moderate`, `settings` FROM $dbName WHERE settings = 'plus' OR settings = 'minus'" );
		$count = 0;

		foreach ( $res as $key => $item ) {
			if ( $res[ $key ]->moderate === '' ) {
				$count ++;
			}
		}


		if ( $count ) {
			foreach ( $menu as $key => &$item ) {
				if ( $item[2] === 'asl_polling' ) {
					$item[0] .= ' <span class="update-plugins"><span class="plugin-count">'
					            . $count
					            . '</span></span>';
					break;
				}
			}
		}
	}

	public function rest_edit_poll( WP_REST_Request $request ) {
		global $wpdb;
		$dbName      = $wpdb->prefix . asl_polling_db_table_name();
		$data        = json_decode( stripslashes( $request->get_param( 'data' ) ), true );
		$id          = $request->get_param( 'id' );
		$old_poll    = get_item( $id );
		$old_pluses  = array_key_exists( 'pluses', $old_poll ) ? $old_poll['pluses'] : [];
		$pluses      = isset( $data['pluses'] ) ? $data['pluses'] : [];
		$old_minuses = array_key_exists( 'minuses', $old_poll ) ? $old_poll['minuses'] : [];
		$minuses     = isset( $data['minuses'] ) ? $data['minuses'] : [];


		$result = array_diff_assoc( $data, $old_poll );

		foreach ( $result as $key => $val ) {
			switch ( $key ) {
				case 'name':
					$wpdb->update( $wpdb->posts, [ 'post_title' => $val ], [ 'ID' => $id ] );
					break;
				case 'description':
					$wpdb->update( $wpdb->posts, [ 'post_content' => $val ], [ 'ID' => $id ] );
					break;
				case 'headPlus':
					$wpdb->update( $dbName, [ 'value' => $val ], [ 'item_id' => $id, 'settings' => 'headPlus' ] );
					break;
				case 'headMinus':
					$wpdb->update( $dbName, [ 'value' => $val ], [ 'item_id' => $id, 'settings' => 'headMinus' ] );
					break;
			}


		}

		if ( is_null( $old_pluses ) and ! is_null( $pluses ) ) {
			foreach ( $pluses as $val ) {
				$wpdb->insert( $dbName, [
					'item_id'    => $id,
					'value'      => $val['text'],
					'settings'   => 'plus',
					'rating'     => $val['rating'],
					'moderate'   => $val['publish'],
					'created_at' => date( "Y-m-d H:i:s" ),
					'updated_at' => date( "Y-m-d H:i:s" ),
				] );
			}
		} else if ( ! is_null( $old_pluses ) and ! is_null( $pluses ) ) {
			$resPlus = array_filter( $pluses, function ( $element ) use ( &$old_pluses ) {
				return ! in_array( $element, $old_pluses );
			} );

			$delTmpPlus = array_filter( $old_pluses, function ( $element ) use ( &$pluses ) {
				return ! in_array( $element, $pluses );
			} );
			$delPlus    = array_filter( $delTmpPlus, function ( $element ) use ( &$resPlus ) {
				return ! in_array( $element, $resPlus );
			} );

			foreach ( $resPlus as $val ) {
				$wpdb->insert( $dbName, [
					'item_id'    => $id,
					'value'      => $val['text'],
					'settings'   => 'plus',
					'rating'     => $val['rating'],
					'moderate'   => $val['publish'],
					'created_at' => date( "Y-m-d H:i:s" ),
					'updated_at' => date( "Y-m-d H:i:s" ),
				] );
			}

			if ( count( $delPlus ) !== 0 ) {
				foreach ( $delPlus as $item ) {
					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM $dbName WHERE id = '%d'",
							$item['id']
						)
					);
				}
			}
		}

		if ( is_null( $old_minuses ) and ! is_null( $minuses ) ) {
			foreach ( $minuses as $val ) {
				$wpdb->insert( $dbName, [
					'item_id'    => $id,
					'value'      => $val['text'],
					'settings'   => 'minus',
					'rating'     => $val['rating'],
					'moderate'   => $val['publish'],
					'created_at' => date( "Y-m-d H:i:s" ),
					'updated_at' => date( "Y-m-d H:i:s" ),
				] );
			}
		} else if ( ! is_null( $old_minuses ) and ! is_null( $minuses ) ) {
			$resMinus = array_filter( $minuses, function ( $element ) use ( &$old_minuses ) {
				return ! in_array( $element, $old_minuses );
			} );

			$delTmpMinus = array_filter( $old_minuses, function ( $element ) use ( &$minuses ) {
				return ! in_array( $element, $minuses );
			} );
			$delMinus    = array_filter( $delTmpMinus, function ( $element ) use ( &$resMinus ) {
				return ! in_array( $element, $resMinus );
			} );

			foreach ( $resMinus as $val ) {
				$wpdb->insert( $dbName, [
					'item_id'    => $id,
					'value'      => $val['text'],
					'settings'   => 'minus',
					'rating'     => $val['rating'],
					'moderate'   => $val['publish'],
					'created_at' => date( "Y-m-d H:i:s" ),
					'updated_at' => date( "Y-m-d H:i:s" ),
				] );
			}

			if ( count( $delMinus ) !== 0 ) {
				foreach ( $delMinus as $item ) {
					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM $dbName WHERE id = '%d'",
							$item['id']
						)
					);
				}
			}
		}
		$new_poll = get_item( $id );
		wp_send_json( $new_poll, 200 );

	}

	public function rest_get_poll( WP_REST_Request $request ): void {
		wp_send_json( get_item( $request->get_param( 'id' ) ), 200 );

	}

	public function enqueue_data_polling_scripts(): void {
		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'asl_polling' ) {
			$this->enqueue_scripts();
			$this->enqueue_styles();
		}
	}

	public function conditional_plugin_admin_notice() {
		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'asl_polling' ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_action( 'network_admin_notices', 'update_nag', 3 );
			echo '<style>.update-nag, .updated, .notice, .error, .is-dismissible { display: none; }</style>';
		}
	}

}
