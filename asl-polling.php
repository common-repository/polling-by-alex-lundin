<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://vk.com/aslundin
 * @since             1.0.1
 * @package           Asl_Polling
 *
 * @wordpress-plugin
 * Plugin Name:       Polling by Alex Lundin
 * Description:       Plugin for creating surveys about a single product, product, service
 * Version:           1.0.1
 * Author:            Alex Lundin
 * Author URI:        https://vk.com/aslundin
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       asl-polling
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ASL_POLLING_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'ASL_POLLING_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'ASL_POLLING_VERSION', '1.0.0' );
define( 'ASL_POLLING_ASSETT_VERSION', '1.0.0' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-asl-polling-activator.php
 */
function activate_asl_polling() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-asl-polling-activator.php';
	Asl_Polling_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-asl-polling-deactivator.php
 */
function deactivate_asl_polling() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-asl-polling-deactivator.php';
	Asl_Polling_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_asl_polling' );
register_deactivation_hook( __FILE__, 'deactivate_asl_polling' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-asl-polling.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_asl_polling() {

	$plugin = new Asl_Polling();
	$plugin->run();

}

run_asl_polling();
