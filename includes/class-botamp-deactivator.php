<?php
/**
 * Fired during plugin deactivation
 *
 * @link       support@botamp.com
 * @since      1.3.2
 *
 * @package    Botamp
 * @subpackage Botamp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.3.2
 * @package    Botamp
 * @subpackage Botamp/includes
 * @author     Botamp, Inc. <support@botamp.com>
 */
class Botamp_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.3.2
	 */
	public static function deactivate() {
		add_rewrite_endpoint( 'botamp_order_unsubscribe', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

}
