<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       support@botamp.com
 * @since      1.3.2
 *
 * @package    Botamp
 * @subpackage Botamp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.3.2
 * @package    Botamp
 * @subpackage Botamp/includes
 * @author     Botamp, Inc. <support@botamp.com>
 */
class Botamp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.3.2
	 * @access   protected
	 * @var      Botamp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.3.2
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.3.2
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.3.2
	 */
	public function __construct() {

		$this->plugin_name = 'botamp';
		$this->version = '1.3.2';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		if ( $this->woocommerce_activated() ) {
			$this->define_woocommerce_admin_hooks();
			$this->define_woocommerce_public_hooks();
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Botamp_Loader. Orchestrates the hooks of the plugin.
	 * - Botamp_i18n. Defines internationalization functionality.
	 * - Botamp_Admin. Defines all hooks for the admin area.
	 * - Botamp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.3.2
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-botamp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-botamp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-botamp-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'botamp-woocommerce/class-botamp-woocommerce-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'botamp-woocommerce/class-botamp-woocommerce-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-botamp-public.php';

		$this->loader = new Botamp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Botamp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.3.2
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Botamp_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Botamp_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'update_option_botamp_api_key', $plugin_admin, 'on_api_key_change', 10, 2 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_all_settings' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_warning_message' );
		$this->loader->add_action( 'wp_ajax_botamp_import', $plugin_admin, 'ajax_import_post' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'create_or_update_entity' );
		$this->loader->add_action( 'wp_trash_post', $plugin_admin, 'on_post_delete' );
		$this->loader->add_action( 'before_delete_post', $plugin_admin, 'on_post_delete' );
		$this->loader->add_action( 'untrashed_post', $plugin_admin, 'create_or_update_entity' );
		$this->loader->add_action( 'widgets_init', $plugin_admin, 'register_widgets' );
	}

	private function define_woocommerce_admin_hooks() {
		$woocommerce_admin = new Botamp_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init', $woocommerce_admin, 'register_settings' );
	}

	private function define_woocommerce_public_hooks() {
		$woocommerce_public = new Botamp_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'woocommerce_after_order_notes', $woocommerce_public, 'add_messenger_widget' );
		$this->loader->add_action( 'woocommerce_checkout_order_processed', $woocommerce_public, 'after_checkout' );
		$this->loader->add_action( 'woocommerce_order_status_processing', $woocommerce_public, 'after_order_status_changed' );
		$this->loader->add_action( 'woocommerce_order_status_completed', $woocommerce_public, 'after_order_status_changed' );
		$this->loader->add_action( 'woocommerce_order_status_refunded', $woocommerce_public, 'after_order_status_changed' );
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $woocommerce_public, 'add_unsubscribe_button',10, 2 );
		$this->loader->add_action( 'woocommerce_after_account_orders', $woocommerce_public, 'add_unsubscribe_all_button' );
		$this->loader->add_filter( 'query_vars', $woocommerce_public, 'add_query_vars', 0 );
		$this->loader->add_action( 'woocommerce_account_botamp_order_unsubscribe_endpoint', $woocommerce_public, 'unsubscribe' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Botamp_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.3.2
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.3.2
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.3.2
	 * @return    Botamp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.3.2
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	private function woocommerce_activated() {
	 	return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

}
