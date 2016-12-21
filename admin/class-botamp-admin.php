<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/option.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/botamp-client.php';

class Botamp_Admin {

	use Option;
	use Botamp_Client;

	private $plugin_name;
	private $version;
	private $fields;
	private $botamp;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		global $wpdb;
		$this->fields = [
		 'post_title',
		 'post_content',
		 'post_excerpt',
		 'post_thumbnail_url',
		 'post_permalink',
		];
		$post_metas = $wpdb->get_col( "select distinct meta_key from {$wpdb->prefix}postmeta
										where meta_key not like 'botamp_%'", 0 );
		$this->fields = array_merge( $this->fields, $post_metas );

		$this->botamp = $this->get_botamp();
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/botamp-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/botamp-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function display_warning_message() {
		$api_key = $this->get_option( 'api_key' );
		if ( empty( $api_key ) ) {
			$html = '<div class="notice notice-warning is-dismissible"> <p>';
			$html .= sprintf( __( 'Please complete the Botamp plugin installation on the <a href="%s">settings page</a>.', 'botamp' ), admin_url( 'options-general.php?page=botamp' ) );
			$html .= '</p> </div>';
			set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
			echo $html;
		} else {
			$auth_status = get_transient( 'botamp_auth_status' );
			if ( false === $auth_status ) {
				try {
					$this->botamp->me->get();
					set_transient( 'botamp_auth_status', 'ok', HOUR_IN_SECONDS );
				} catch (Botamp\Exceptions\Unauthorized $e) {
					set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
				}

				$this->display_warning_message();
			} elseif ( 'unauthorized' === $auth_status ) {
				$html = '<div class="notice notice-warning is-dismissible"> <p>';
				$html .= sprintf( __( 'Authentication with the provided API key is not working.<br/>
Please provide a valid API key on the <a href="%s">settings page</a>.', 'botamp' ), admin_url( 'options-general.php?page=botamp' ) );
				$html .= '</p> </div>';
				echo $html;
			}
		}

	}

	public function add_options_page() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Botamp Application Settings', 'botamp' ),
			__( 'Botamp', 'botamp' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);

	}

	public function display_options_page() {
		include_once 'partials/botamp-admin-display.php';
	}

	public function register_settings() {
		// Add a General section
		add_settings_section(
			$this->option( 'general' ),
			__( 'General', 'botamp' ),
			array( $this, 'general_cb' ),
			$this->plugin_name
		);

		// Add a Entity section
		add_settings_section(
			$this->option( 'entity' ),
			__( 'Content Mapping', 'botamp' ),
			array( $this, 'entity_cb' ),
			$this->plugin_name
		);

		// Add API key field
		add_settings_field(
			$this->option( 'api_key' ),
			__( 'API key', 'botamp' ),
			array( $this, 'api_key_cb' ),
			$this->plugin_name,
			$this->option( 'general' ),
			array( 'label_for' => $this->option( 'api_key' ) )
		);

		// Add Post type field
		add_settings_field(
			$this->option( 'post_type' ),
			__( 'Post type', 'botamp' ),
			array( $this, 'post_type_cb' ),
			$this->plugin_name,
			$this->option( 'general' ),
			array( 'label_for' => $this->option( 'post_type' ) )
		);

		add_settings_field(
			$this->option( 'entity_description' ),
			__( 'Description', 'botamp' ),
			array( $this, 'entity_description_cb' ),
			$this->plugin_name,
			$this->option( 'entity' ),
			array( 'label_for' => $this->option( 'entity_description' ) )
		);

		add_settings_field(
			$this->option( 'entity_image_url' ),
			__( 'Image URL', 'botamp' ),
			array( $this, 'entity_image_url_cb' ),
			$this->plugin_name,
			$this->option( 'entity' ),
			array( 'label_for' => $this->option( 'entity_image_url' ) )
		);

		add_settings_field(
			$this->option( 'entity_title' ),
			__( 'Title', 'botamp' ),
			array( $this, 'entity_title_cb' ),
			$this->plugin_name,
			$this->option( 'entity' ),
			array( 'label_for' => $this->option( 'entity_title' ) )
		);

		add_settings_field(
			$this->option( 'entity_url' ),
			__( 'URL', 'botamp' ),
			array( $this, 'entity_url_cb' ),
			$this->plugin_name,
			$this->option( 'entity' ),
			array( 'label_for' => $this->option( 'entity_url' ) )
		);

		register_setting( $this->plugin_name, $this->option( 'api_key' ) );
		register_setting( $this->plugin_name, $this->option( 'post_type' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_description' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_image_url' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_title' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_url' ) );

	}

	public function general_cb() {
		echo '<p>'
			. __( 'Visit <a href="https://app.botamp.com">your bot settings page on Botamp</a> to get your API key.', 'botamp' )
			. '</p>';
	}

	public function entity_cb() {
		echo '<p>'
			. __( 'Choose the post fields your bot will use to respond to your customers.', 'botamp' )
			. '</p>';
	}

	public function api_key_cb() {
		$api_key = $this->get_option( 'api_key' );
		echo '<input type="text" name="' . $this->option( 'api_key' ) . '" value="' . $api_key . '" class="regular-text"> ';
	}

	public function post_type_cb() {
		$current_post_type = $this->get_option( 'post_type' );

		$html = '<select name = "' . $this->option( 'post_type' ) . '" class = "regular-list" >';
		foreach ( get_post_types( '', 'objects' ) as $post_type ) {
			if ( $current_post_type === $post_type->name ) {
				$html .= "<option value = '{$post_type->name}' selected='true'>{$post_type->label} </option>";
			} else {
				$html .= "<option value = '{$post_type->name}'> {$post_type->label} </option>";
			}
		}
		$html .= '</select>';

		echo $html;

	}

	public function entity_description_cb() {
		echo $this->print_field_select( 'entity_description' );
	}

	public function entity_image_url_cb() {
		echo $this->print_field_select( 'entity_image_url' );
	}

	public function entity_title_cb() {
		echo $this->print_field_select( 'entity_title' );
	}

	public function entity_url_cb() {
		echo $this->print_field_select( 'entity_url' );
	}

	private function print_field_select( $option ) {
		$option_value = $this->get_option( $option );

		$html = '<select name = "' . $this->option( $option ) . ' " class = "regular-list" >';
		foreach ( $this->fields as $field ) {
			if ( $option_value === $field ) {
				$html .= "<option value = '$field' selected='true'>"
				. $this->field_name( $field )
				. '</option>';
			} else {
				$html .= "<option value = '$field'>"
				. $this->field_name( $field )
				. '</option>';
			}
		}
		return $html;
	}

	private function field_name( $field ) {
		switch ( $field ) {
			case 'post_title':
				return __( 'Post title', 'botamp' );
			case 'post_content':
				return __( 'Post content', 'botamp' );
			case 'post_excerpt':
				return __( 'Post excerpt', 'botamp' );
			case 'post_thumbnail_url':
				return __( 'Post thumbnail URL', 'botamp' );
			case 'post_permalink':
				return __( 'Post permalink', 'botamp' );
			default:
				return $field;
		}
	}
}
