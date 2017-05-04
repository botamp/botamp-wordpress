<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       support@botamp.com
 * @since      1.3.2
 *
 * @package    Botamp
 * @subpackage Botamp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.3.2
 * @package    Botamp
 * @subpackage Botamp/includes
 * @author     Botamp, Inc. <support@botamp.com>
 */
class Botamp_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.3.2
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'botamp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
