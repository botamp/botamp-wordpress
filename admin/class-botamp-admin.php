<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/option-helper.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/proxy-helper.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-optinwidget.php';

class Botamp_Admin {

	use OptionHelper;
	use ProxyHelper;

	private $plugin_name;
	private $version;
	private $fields;
	private $post_types;

	public function __construct( $plugin_name, $version ) {
        global $wp_post_types;

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->post_types = $this->get_post_types();

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
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/botamp-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/botamp-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function import_all_posts() {
		include_once 'partials/botamp-admin-display-import.php';
	}

	public function ajax_import_post() {
		// @codingStandardsIgnoreStart
		@error_reporting( 0 ); // Don't break the JSON result
		// @codingStandardsIgnoreEnd

		header( 'Content-type: application/json' );

		$post_id = (int) $_REQUEST['post_id'];

		if ( $this->create_or_update_entity( $post_id ) === true ) {
			die( json_encode( array(
				// translators: The placeholder parameter is the title of the imported post
				'success' => sprintf( __( 'The post <i>%s</i> was successfully imported' ), get_the_title( $post_id ) ),
			) ) );
		} else {
			die( json_encode( array(
				// translators: The placeholder parameter is the title of the post failed to import
				'error' => sprintf( __( 'The post <i>%s</i> failed to import' ), get_the_title( $post_id ) ),
			) ) );
		}
	}

	public function display_warning_message() {
		$auth_status = $this->get_auth_status();
		$html = '<div class="notice notice-warning is-dismissible"> <p>';

		if ( 'api_key_not_set' == $auth_status ) {
			// translators: The placeholder parameter is the url to the plugin settings page
			$html .= sprintf( __( 'Please complete the Botamp plugin installation on the <a href="%s">settings page</a>.', 'botamp' ), admin_url( 'options-general.php?page=botamp' ) );
		} elseif ( 'unauthorized' == $auth_status ) {
			// translators: The placeholder parameter is the url to the plugin settings page
			$html .= sprintf( __( 'Authentication with the provided API key is not working.<br/>
Please provide a valid API key on the <a href="%s">settings page</a>.', 'botamp' ), admin_url( 'options-general.php?page=botamp' ) );
		} else { return;
		}

		echo $html .= '</p> </div>';
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

	public function register_all_settings() {
		$section_page = $this->option( 'general_section' );
		$fields_group = $this->option( 'general_group' );

		add_settings_section(
			$this->option( 'general' ),
			__( 'General', 'botamp' ),
			array( $this, 'general_cb' ),
			$this->option( 'general_section' )
		);

		add_settings_field(
			$this->option( 'api_key' ),
			__( 'API key', 'botamp' ),
			array( $this, 'api_key_cb' ),
			$section_page,
			$this->option( 'general' ),
			array(
				'label_for' => $this->option( 'api_key' ),
			)
		);

		register_setting( $fields_group, $this->option( 'api_key' ) );
		register_setting( $fields_group, $this->option( 'optin' ) );

		if ( $this->get_auth_status() == 'ok' ) {
			foreach ( $this->post_types as $post_type ) {
				$this->register_settings( $post_type->name );
			}
		}
	}

	public function register_settings( $post_type_name ) {
		$section_page = $this->option( "{$post_type_name}_entity_section" );
		$fields_group = $this->option( "{$post_type_name}_group" );

		add_settings_section(
			$this->option( 'entity' ),
			__( 'Content Mapping', 'botamp' ),
			array( $this, 'entity_cb' ),
			$section_page
		);

		add_settings_field(
			$this->option( 'post_type' ),
			__( 'Post type', 'botamp' ),
			array( $this, 'post_type_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
				'label_for' => $this->option( 'post_type' ),
			)
		);

		add_settings_field(
			$this->option( "{$post_type_name}_sync" ),
			__( 'Sync this post type', 'botamp' ),
			array( $this, 'post_type_sync_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
			'label_for' => $this->option( 'post_type_sync' ),
			 'post_type_name' => $post_type_name,
			)
		);

		add_settings_field(
			$this->option( 'entity_type' ),
			__( 'Entity type', 'botamp' ),
			array( $this, 'entity_type_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
				'label_for' => $this->option( 'entity_type' ),
				 'post_type_name' => $post_type_name,
			)
		);

		add_settings_field(
			$this->option( "{$post_type_name}_entity_description" ),
			__( 'Description', 'botamp' ),
			array( $this, 'entity_description_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
			'label_for' => $this->option( 'entity_description' ),
				   'post_type_name' => $post_type_name,
			)
		);

		add_settings_field(
			$this->option( "{$post_type_name}_entity_image_url" ),
			__( 'Image URL', 'botamp' ),
			array( $this, 'entity_image_url_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
			'label_for' => $this->option( 'entity_image_url' ),
			 	   'post_type_name' => $post_type_name,
			)
		);

		add_settings_field(
			$this->option( "{$post_type_name}_entity_title" ),
			__( 'Title', 'botamp' ),
			array( $this, 'entity_title_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
			'label_for' => $this->option( 'entity_title' ),
			 	   'post_type_name' => $post_type_name,
			)
		);

		add_settings_field(
			$this->option( "{$post_type_name}_entity_url" ),
			__( 'URL', 'botamp' ),
			array( $this, 'entity_url_cb' ),
			$section_page,
			$this->option( 'entity' ),
			array(
			'label_for' => $this->option( 'entity_url' ),
			 	   'post_type_name' => $post_type_name,
			)
		);

		register_setting( $fields_group, $this->option( 'post_type' ) );
		register_setting( $fields_group, $this->option( "{$post_type_name}_entity_type" ) );
		register_setting( $fields_group, $this->option( "{$post_type_name}_sync" ) );
		register_setting( $fields_group, $this->option( "{$post_type_name}_entity_description" ) );
		register_setting( $fields_group, $this->option( "{$post_type_name}_entity_image_url" ) );
		register_setting( $fields_group, $this->option( "{$post_type_name}_entity_title" ) );
		register_setting( $fields_group, $this->option( "{$post_type_name}_entity_url" ) );
	}

	public function register_widgets() {
		$optin_widget_class = 'OptinWidget';

		if ( $this->get_option( 'optin' ) ) {
			register_widget( $optin_widget_class );
		} else {
			unregister_widget( $optin_widget_class );
		}
	}

	public function general_cb() {
		echo '<p>'
			. __( 'Visit <a href="https://app.botamp.com">your settings page on Botamp</a> to get your API key.', 'botamp' )
			. '</p>';
	}

	public function entity_cb() {
		echo '<p>'
			. __( 'Choose the post fields to sync with Botamp.', 'botamp' )
			. '</p>';
	}

	public function api_key_cb() {
		$api_key = $this->get_option( 'api_key' );
		echo '<input type="text" name="' . $this->option( 'api_key' ) . '" value="' . $api_key . '" class="regular-text"> ';
	}

	public function post_type_cb() {
		$current_post_type = isset( $_GET['post-type'] ) ? $_GET['post-type'] : 'post';

		$html = '<select name = "' . $this->option( 'post_type' ) . '"class = "regular-list"
		 onchange="window.location.href=this.value">';

		foreach ( $this->post_types as $post_type ) {
			$url = add_query_arg( 'post-type', $post_type->name, admin_url( 'options-general.php?page=botamp' ) );

			$selected_attribute = $current_post_type === $post_type->name ? 'selected' : '';
			$html .= "<option value = '{$url}' {$selected_attribute}>{$post_type->label} </option>";
		}

		echo $html .= '</select>';
	}

	public function post_type_sync_cb( $args ) {
		$current_state = $this->get_option( "{$args['post_type_name']}_sync" );

		echo '<input type="checkbox" name="' . $this->option( "{$args['post_type_name']}_sync" ) . '" value="enabled" ' .
		checked( 'enabled', $current_state, false ) . '/>';
	}

	public function entity_type_cb( $args ) {
		$current_entity_type = $this->get_option( "{$args['post_type_name']}_entity_type" );

		$html = '<select name = "' . $this->option( "{$args['post_type_name']}_entity_type" ) . '"class = "regular-list">';
		$html .= '<option value="" selected></option>';

		$entity_types = $this->get_proxy( 'entity_type' )->all()->getBody()['data'];

		foreach ( $entity_types as $entity_type ) {
			$selected_attribute = $current_entity_type === $entity_type['attributes']['name'] ? 'selected' : '';
			$html .= "<option value = '{$entity_type['attributes']['name']}' {$selected_attribute}>{$entity_type['attributes']['singular_label']} </option>";
		}

		echo $html .= '</select>';
	}

	public function entity_description_cb( $args ) {
		echo $this->print_field_select( "{$args['post_type_name']}_entity_description" );
	}

	public function entity_image_url_cb( $args ) {
		$fields = [ 'post_thumbnail_url' ];

		echo $this->print_field_select( "{$args['post_type_name']}_entity_image_url", $fields );
	}

	public function entity_title_cb( $args ) {
		echo $this->print_field_select( "{$args['post_type_name']}_entity_title" );
	}

	public function entity_url_cb( $args ) {
		echo $this->print_field_select( "{$args['post_type_name']}_entity_url" );
	}

	public function create_or_update_entity( $post_id ) {
		if ( ! ( $this->get_option( get_post_type( $post_id ) . '_sync' ) === 'enabled'
				 && get_post_status( $post_id ) === 'publish' ) ) {
				return;
		}

		$params = $this->get_fields_values( $post_id );
		foreach ( [ 'description', 'url', 'title' ] as $field ) {
			if ( ! isset( $params[ $field ] )
				|| empty( $params[ $field ] )
				|| false == $params[ $field ] ) {
					return false;
			}
		}

		$product_entity_proxy = $this->get_proxy( 'product_entity' );

		$entity_id = $this->get_post_botamp_id( $post_id );
		if ( ! empty( $entity_id ) ) {
			$response = $product_entity_proxy->update( $entity_id, $params );
			if ( false !== $response ) {
				update_post_meta( $post_id, 'botamp_entity_id', $response->getBody()['data']['id'] );
				return true;
			}
		} else {
			$response = $product_entity_proxy->create( $params );
			if ( false !== $response ) {
				add_post_meta( $post_id, 'botamp_entity_id', $response->getBody()['data']['id'] );
				return true;
			}
		}
		return false;
	}

	public function on_post_delete( $post_id ) {
	    $entity_id = $this->get_post_botamp_id( $post_id );
		if ( get_post_type( $post_id ) === $this->get_option( 'post_type' )
			&& ! empty( $entity_id ) ) {
			$this->get_proxy( 'product_entity' )->delete( $entity_id );
			delete_post_meta( $post_id, 'botamp_entity_id' );
		}
	}

	public function on_api_key_change( $old_api_key, $new_api_key ) {
		$me_proxy = $this->get_proxy( 'me' );
		$me_proxy->set_botamp_client( $new_api_key );
		$me_proxy->get();
	}

	private function get_post_types() {
        $post_types = get_post_types( '', 'objects');

        if( !in_array( 'product', array_map(function($post_type) { return $post_type->name; }, $post_types ) )
         && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

            $product_post_type = new stdClass();
            $product_post_type->name = 'product';
            $product_post_type->label = 'Product';

            $post_types[] = $product_post_type;
        }

        return $post_types;
    }

	private function print_field_select( $option, $fields = [] ) {
		$option_value = $this->get_option( $option );

		$fields = empty( $fields ) ? $this->fields : $fields;

		$html = '<select name = "' . $this->option( $option ) . '" class = "regular-list" >';
		$html .= '<option value=""></option>';

		foreach ( $fields as $field ) {
			$selected_attribute = $option_value === $field ? 'selected' : '';
			$html .= "<option value='$field' {$selected_attribute}>{$this->field_name( $field )}</option>";
		}

		return $html .= '</select>';
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

	private function get_auth_status() {
		if ( empty( $this->get_option( 'api_key' ) ) ) {
			return 'api_key_not_set';
		}

		$auth_status = get_transient( 'botamp_auth_status' );
		if ( false === $auth_status ) {
			$this->get_proxy( 'me' )->get();
			$auth_status = get_transient( 'botamp_auth_status' );
		}

		return $auth_status;
	}

	private function get_post_botamp_id( $post_id ) {
		return get_post_meta( $post_id, 'botamp_entity_id', true );
	}

	private function get_fields_values( $post_id ) {
		$post = get_post( $post_id, ARRAY_A );
		$post_type = get_post_type( $post_id );

		$values = [
			'entity_type' => $this->get_option( "{$post_type}_entity_type" ),
		];

		foreach ( [ 'description', 'url', 'image_url', 'title' ] as $field ) {
			$option = $this->get_option( "{$post_type}_entity_{$field}" );
			switch ( $option ) {
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
				case '';
					break;
				default:
					$values[ $field ] = get_post_meta( $post_id, $option, true );
					break;
			}
		}
		return $values;
	}
}
