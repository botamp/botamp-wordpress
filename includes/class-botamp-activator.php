<?php
/**
 * Fired during plugin activation
 *
 * @link       support@botamp.com
 * @since      1.3.2
 *
 * @package    Botamp
 * @subpackage Botamp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.3.2
 * @package    Botamp
 * @subpackage Botamp/includes
 * @author     Botamp, Inc. <support@botamp.com>
 */

class Botamp_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.3.2
	 */
	public static function activate() {
		add_rewrite_endpoint( 'botamp_order_unsubscribe', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

}
