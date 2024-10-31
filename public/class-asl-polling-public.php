<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Polling
 * @subpackage Asl_Polling/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Asl_Polling
 * @subpackage Asl_Polling/public
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class Asl_Polling_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}


	public function get_polls_block() {
		global $wpdb;
		$dbName      = $wpdb->prefix . asl_polling_db_table_name();
		$response    = [];
		$headPost    = $wpdb->get_results( "SELECT ID, post_title, post_content FROM $wpdb->posts WHERE `post_type` = '$this->cpt_name'" );
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
		for ( $i = 0; $i <= (int) count( $contentPost ); $i ++ ) {
			for ( $k = 0; $k <= (int) count( $response ); $k ++ ) {
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
		wp_die();
	}

	public function register_block() {
		wp_register_script( 'asl-poll-block', plugin_dir_url( __FILE__ ) . '/block/build/js/asl-poll-block.js', array(
			'wp-api-fetch',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n'
		) );
		register_block_type( 'asl/asl-polls', [
				'api_version'     => 2,
				'editor_script'   => 'asl-poll-block',
				'render_callback' => array( $this, 'asl_block_render' )
			]
		);
	}

	function asl_public_set_script_translations() {
		wp_set_script_translations( 'asl-poll-block', 'asl-polling', plugin_dir_path( __FILE__ ) . 'languages' );
		wp_set_script_translations( $this->plugin_name . '-manifest', 'asl-polling', plugin_dir_path( __FILE__ ) . 'languages' );
		wp_set_script_translations( $this->plugin_name . '-vendor', 'asl-polling', plugin_dir_path( __FILE__ ) . 'languages' );
		wp_set_script_translations( $this->plugin_name . '-app', 'asl-polling', plugin_dir_path( __FILE__ ) . 'languages' );
	}

	/**
	 * @param $attr
	 *
	 * @return string
	 */
	public function asl_block_render( $attr ): string {
		$id                 = $attr['pollId'];
		$skin               = empty( $attr['skin'] ) ? 'columns' : $attr['skin'];
		$displayHead        = empty( $attr['displayHead'] ) ? 'no' : 'yes';
		$displayDescription = empty( $attr['displayDescription'] ) ? 'no' : 'yes';

		return '<div class="asl-poll" id="asl-poll-' . $id . '" data-poll="' . $id . '" data-skin="' . $skin . '" data-head="' . $displayHead . '" data-description="' . $displayDescription . '"></div>';
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Asl_Polling_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Asl_Polling_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/asl-polling-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Asl_Polling_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Asl_Polling_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name . '-manifest', plugin_dir_url( __FILE__ ) . 'js/prod/js/manifest.js', array( 'wp-i18n' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-vendor', plugin_dir_url( __FILE__ ) . 'js/prod/js/vendor.js', array( $this->plugin_name . '-manifest' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-app', plugin_dir_url( __FILE__ ) . 'js/prod/js/poll.js', array( $this->plugin_name . '-vendor' ), $this->version, true );


		wp_localize_script( $this->plugin_name . '-manifest', 'asl_poll', array(
			'asl_rest_uri' => get_rest_url(),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'url'          => admin_url( 'admin-ajax.php' )
		) );
	}

	public function update_rating_poll() {
		global $wpdb;
		$dbName = $wpdb->prefix . asl_polling_db_table_name();
		$rating = json_decode( sanitize_text_field( stripslashes( $_POST['rating'] ), true ) );
		$id     = json_decode( sanitize_text_field( stripslashes( $_POST['idPoll'] ), true ) );
		$idItem = json_decode( sanitize_text_field( stripslashes( $_POST['id'] ), true ) );

		$wpdb->update( $dbName, [ 'rating' => $rating ], [ 'id' => $id ] );
		wp_send_json( get_item( $idItem ) );
		wp_die();
	}

	public function add_item_poll() {
		global $wpdb;
		$dbName  = $wpdb->prefix . asl_polling_db_table_name();
		$id      = sanitize_text_field( $_POST['id'] );
		$setting = sanitize_text_field( $_POST['value'] );
		$text    = sanitize_text_field( $_POST['text'] );


		$wpdb->insert( $dbName, [
			'item_id'  => $id,
			'settings' => $setting,
			'rating'   => 1,
			'moderate' => false,
			'value'    => $text
		] );

		wp_send_json( get_item( $id ) );
		wp_die();
	}
}
