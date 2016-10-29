<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       support@botamp.com
 * @since      1.0.0
 *
 * @package    Botamp
 * @subpackage Botamp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Botamp
 * @subpackage Botamp/public
 * @author     Botamp, Inc. <support@botamp.com>
 */
class Botamp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The botamp client object
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Botamp\Client         $botamp
	 */
	private $botamp;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->botamp = new Botamp\Client( get_option( 'botamp_api_key' ) );
		if ( defined( 'BOTAMP_API_BASE' ) ) {
			$this->botamp->setApiBase( BOTAMP_API_BASE );
		}
	}

	public function create_or_update_entity( $post_id ) {
		if ( get_post_type( $post_id ) === get_option( 'botamp_post_type' )
				&& get_post_status( $post_id ) === 'publish' ) {
			$params = $this->get_fields_values( $post_id );
			foreach ( [ 'description', 'url', 'image_url', 'title' ] as $field ) {
				if ( ! isset( $params[ $field ] )
					|| empty( $params[ $field ] )
					|| false == $params[ $field ] ) {
						return;
				}
			}


			if ( ! empty( $entity_id = get_post_meta( $post_id, 'botamp_entity_id', true ) ) ) {
				try {
					$response = $this->botamp->entities->get( $entity_id );
					$this->botamp->entities->update( $entity_id, $params );
					$this->set_auth_status( 'ok' );
				} catch (Botamp\Exceptions\NotFound $e) {
					$response = $this->botamp->entities->create( $params );
					update_post_meta( $post_id,
						'botamp_entity_id',
					$response->getBody()['data']['id']);
				} catch (Botamp\Exceptions\Unauthorized $e) {
					$this->set_auth_status( 'unauthorized' );
				}
			} else {
				try {
					$response = $this->botamp->entities->create( $params );
					add_post_meta( $post_id, 'botamp_entity_id', $response->getBody()['data']['id'] );
					$this->set_auth_status( 'ok' );
				} catch (Botamp\Exceptions\Unauthorized $e) {
					$this->set_auth_status( 'unauthorized' );
				}
			}
		}
	}

	public function delete_entity( $post_id ) {
		if ( get_post_type( $post_id ) === get_option( 'botamp_post_type' )
		  	&& ! empty( $entity_id = get_post_meta( $post_id, 'botamp_entity_id', true ) ) ) {
			try {
				$this->botamp->entities->delete( $entity_id );
				$this->set_auth_status( 'ok' );
			} catch (Botamp\Exceptions\Unauthorized $e) {
				$this->set_auth_status( 'unauthorized' );
			}
		}
	}

	private function set_auth_status( $auth_status ) {
		if ( ! in_array( $auth_status, [ 'ok', 'unauthorized' ] ) ) {
			return;
		}
		if ( get_transient( 'botamp_auth_status' ) !== $auth_status  ) {
			set_transient( 'botamp_auth_status', $auth_status, HOUR_IN_SECONDS );
		}
	}

	private function set_operation_status( $operation_status ) {
		if ( ! in_array( $operation_status, [ 'ok', 'not_ok' ] ) ) {
			return;
		}
		if ( get_transient( 'botamp_operation_status' ) !== $operation_status ) {
			set_transient( 'botamp_operation_status', $operation_status, HOUR_IN_SECONDS );
		}
	}

	private function get_fields_values( $post_id ) {
		$post = get_post( $post_id, ARRAY_A );
		$values = [];
		foreach ( [ 'description', 'url', 'image_url', 'title' ] as $field ) {
			switch ( $option = get_option( 'botamp_entity_' . $field ) ) {
				case 'post_title':
					$values[ $field ] = apply_filters( 'the_title', $post['post_title'], $post_id );
					break;
				case 'post_excerpt':
					$values[ $field ] = apply_filters( 'get_the_excerpt', $post['post_excerpt'], $post_id );
					break;
				case 'post_content':
					$values[ $field ] = get_post_field( 'post_content', $post_id );
					break;
				case 'post_permalink';
					$values[ $field ] = get_the_permalink( $post_id );
					break;
				case 'post_thumbnail_url';
					$values[ $field ] = get_the_post_thumbnail_url( $post_id, 'full' );
					break;
				default:
					$values[ $field ] = get_post_meta( $post_id, $option, true );
					break;
			}
		}
		return $values;
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
		 * defined in Botamp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Botamp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/botamp-public.css', array(), $this->version, 'all' );

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
		 * defined in Botamp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Botamp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/botamp-public.js', array( 'jquery' ), $this->version, false );

	}

}
