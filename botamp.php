<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              support@botamp.com
 * @since             1.3.2
 * @package           Botamp
 *
 * @wordpress-plugin
 * Plugin Name:       botamp
 * Plugin URI:        https://botamp.com
 * Description:       Botamp plugin for Wordpress. The easiest way to sync your WordPress site content with Botamp.
 * Version:           1.3.2
 * Author:            Botamp, Inc. <support@botamp.com>
 * Author URI:        support@botamp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       botamp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-botamp-activator.php
 */
function activate_botamp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-botamp-activator.php';
	Botamp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-botamp-deactivator.php
 */
function deactivate_botamp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-botamp-deactivator.php';
	Botamp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_botamp' );
register_deactivation_hook( __FILE__, 'deactivate_botamp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-botamp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.3.2
 */
function run_botamp() {

	$plugin = new Botamp();
	$plugin->run();

}
run_botamp();
