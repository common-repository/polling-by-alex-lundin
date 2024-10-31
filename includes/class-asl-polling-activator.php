<?php

/**
 * Fired during plugin activation
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Polling
 * @subpackage Asl_Polling/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Asl_Polling
 * @subpackage Asl_Polling/includes
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class Asl_Polling_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate(): void {
		self::create_database_table();
	}

	public static function create_database_table(): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . asl_polling_db_table_name();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name" ) != $table_name ) {
			$sql
				= "CREATE TABLE $table_name(
    			id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    			item_id int(11) NOT NULL,
    			moderate varchar(255) NOT NULL,
    			rating int(11) NOT NULL,
    			value longtext,
    			settings longtext,
    			created_at timestamp NULL,
				updated_at timestamp NULL
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

}
