<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link  support@botamp.com
 * @since 1.0.0
 *
 * @package    Botamp
 * @subpackage Botamp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Botamp
 * @subpackage Botamp/admin
 * @author     Botamp, Inc. <support@botamp.com>
 */
class Botamp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The list of all post fields and custom post fields
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Array         $fields
	 */
	private $fields;

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
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
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

	public function get_botamp() {
		$botamp = new Botamp\Client( $this->get_option( 'api_key' ) );
		if ( defined( 'BOTAMP_API_BASE' ) ) {
			$botamp->setApiBase( BOTAMP_API_BASE );
		}
		return $botamp;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/botamp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/botamp-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Display a warning message on plugin activation
	 *
	 * @since 1.0.0
	 * @since 1.0.0
	 */
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

	/**
	 * Add an options page under the Settings submenu
	 *
	 * @since 1.0.0
	 */
	public function add_options_page() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Botamp Application Settings', 'botamp' ),
			__( 'Botamp', 'botamp' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);

	}

	/**
	 * Render the options page for plugin
	 *
	 * @since 1.0.0
	 */
	public function display_options_page() {
		include_once 'partials/botamp-admin-display.php';
	}

	public function register_setting() {
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

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_settings_field(
				$this->option( 'order_notifications' ),
				__( 'Order notifications', 'botamp' ),
				array( $this, 'order_notifications_cb' ),
				$this->plugin_name,
				$this->option( 'general' ),
				array( 'label_for' => $this->option( 'order_notifications' ) )
			);

			register_setting( $this->plugin_name, $this->option( 'order_notifications' ) );
		}

		register_setting( $this->plugin_name, $this->option( 'api_key' ) );
		register_setting( $this->plugin_name, $this->option( 'post_type' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_description' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_image_url' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_title' ) );
		register_setting( $this->plugin_name, $this->option( 'entity_url' ) );

	}

	/**
	 * Render the text for the general section
	 *
	 * @since 1.0.0
	 */
	public function general_cb() {
		echo '<p>'
			. __( 'Visit <a href="https://app.botamp.com">your bot settings page on Botamp</a> to get your API key.', 'botamp' )
			. '</p>';
	}

	/**
	 * Render the text for the entity section
	 *
	 * @since 1.0.0
	 */
	public function entity_cb() {
		echo '<p>'
			. __( 'Choose the post fields your bot will use to respond to your customers.', 'botamp' )
			. '</p>';
	}

	/**
	 * Render the API key input for this plugin
	 *
	 * @since 1.0.0
	 */
	public function api_key_cb() {
		$api_key = $this->get_option( 'api_key' );
		echo '<input type="text" name="' . $this->option( 'api_key' ) . '" value="' . $api_key . '" class="regular-text"> ';
	}

	/**
	 * Render the post type input for this plugin
	 *
	 * @since 1.0.0
	 */
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

	/**
	 * Render checkbox to enable or disable order notifications
	 *
	 * @since 1.0.0
	 */
	public function order_notifications_cb() {
		$current_state = $this->get_option( 'order_notifications' );

		$html = '<input type="checkbox" name="' . $this->option( 'order_notifications' ) . '" value="enabled" ' .
		checked( 'enabled', $current_state, false ) . '/>';
			$html .= '<label for="' . $this->option( 'order_notifications' ) . '"> Send order notifications to users </label>';

		echo $html;

	}

	/**
	 * Render the Entity description input for this plugin
	 *
	 * @since 1.0.0
	 */
	public function entity_description_cb() {
		echo $this->print_field_select( 'entity_description' );
	}

	/**
	 * Render the Entity image URL input for this plugin
	 *
	 * @since 1.0.0
	 */
	public function entity_image_url_cb() {
		echo $this->print_field_select( 'entity_image_url' );
	}

	/**
	 * Render the Entity title input for this plugin
	 *
	 * @since 1.0.0
	 */
	public function entity_title_cb() {
		echo $this->print_field_select( 'entity_title' );
	}

	/**
	 * Render the Entity URL input for this plugin
	 *
	 * @since 1.0.0
	 */
	public function entity_url_cb() {
		echo $this->print_field_select( 'entity_url' );
	}

	public function add_messenger_widget( $checkout ) {
		if ( $this->get_option( 'order_notifications' ) !== 'enabled' ) {
			return;
		}

		$page_attributes = $this->botamp->me->get()->getBody()['data']['attributes'];

		$ref = uniqid( 'botamp_' . $_SERVER['HTTP_HOST'] . '_', true );

		echo '<input type="hidden" name="botamp_contact_ref" value="' . $ref . '">';

		echo '<div id="notifications"><h3>' . __( 'Notifications' ) . '</h3>';
		echo "<script>
			 	window.fbAsyncInit = function() {
				    FB.init({
				      appId      : '{$page_attributes['facebook_app_id']}',
				      xfbml      : true,
				      version    : 'v2.6'
				    });
				};

			  	(function(d, s, id){
				    var js, fjs = d.getElementsByTagName(s)[0];
				      if (d.getElementById(id)) {return;}
				      js = d.createElement(s); js.id = id;
				      js.src = '//connect.facebook.net/en_US/sdk.js';
				      fjs.parentNode.insertBefore(js, fjs);
				  }(document, 'script', 'facebook-jssdk')
			  	);
			</script>
			<div class='fb-send-to-messenger'
			  messenger_app_id='{$page_attributes['facebook_app_id']}'
			  page_id='{$page_attributes['facebook_id']}'
			  data-ref='$ref'
			  color='blue'
			  size='standard'>
			</div></div>";

	}

	public function after_checkout( $order_id ) {
		$order = new WC_Order( $order_id );
		add_post_meta( $order->post->ID, 'botamp_contact_ref', $_POST['botamp_contact_ref'] );
	}

	public function after_status_change( $order_id ) {
		$order = new WC_Order( $order_id );

		$contact = $this->get_contact( $order );

		if ( $contact === false ) {
			return;
		}

		if ( $this->order_created( $order ) ) {
			$this->update_entity( $entity_id, $order );
		} else {
			$entity = $this->create_entity( $order );
			$subscription = $this->create_subscription( $entity, $contact, $order );
		}
	}

	private function create_entity( $order ) {
		$order_meta = $this->get_order_meta( $order );

		$entity_attributes = [
			'title' => $order_meta['order_number'] . ' - ' . $order_meta['recipient_name'],
			'url' => $order_meta['order_url'],
			'entity_type' => 'order',
			'status' => $order->get_status(),
			'meta' => $order_meta,
		];

		$entity = $this->botamp->entities->create( $entity_attributes );
		add_post_meta( $order->post->ID, 'botamp_entity_id', $entity->getBody()['data']['id'] );

		return $entity;
	}

	private function update_entity( $entity_id, $order ) {
		$entity = $this->botamp->entities->get( $entity_id );
		$entity_attributes = $entity->getBody()['data']['attributes'];
		$entity_attributes['status'] = $order->get_status();

		$this->botamp->entities->update( $entity_id, $entity_attributes );
	}

	private function create_subscription( $entity, $contact, $order ) {
		$subscription_attributes = [
			'entity_id' => $entity->getBody()['data']['id'],
			'subscription_type' => $entity->getBody()['data']['attributes']['entity_type'],
			'contact_id' => $contact->getBody()['data']['id']
		];

		$subscription = $this->botamp->subscriptions->create( $subscription_attributes );

		add_post_meta( $order->post->ID, 'botamp_subscription_id', $subscription->getBody()['data']['id'] );

		return $subscription;
	}

	private function get_contact( $order ) {
		$ref = get_post_meta( $order->post->ID, 'botamp_contact_ref', true );

		try{
			$contact = $this->botamp->contacts->get($ref);
			return $contact;
		} catch(Botamp\Exceptions\NotFound $ex) {
			return false;
		}
	}

	private function order_created( $order ) {
		return ! empty( get_post_meta( $order->post->ID, 'botamp_entity_id', true ) );
	}

	private function get_order_meta( $order ) {

		$order_meta = [
			'recipient_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'order_number' => $order->get_order_number(),
			'currency' => $order->order_currency,
			'payment_method' => $order->payment_method_title,
			'order_url' => $order->get_view_order_url(),
			'timestamp' => strtotime( $order->order_date ),
			'address' => [
				'street_1' => $order->billing_address_1,
				'street_2' => $order->billing_address_2,
				'city' => $order->billing_city,
				'postal_code' => $order->billing_postcode,
				'state' => $order->billing_state,
				'country' => $order->billing_country,
			],
			'elements' => [],
			'summary' => [
				'subtotal' => $order->get_subtotal(),
				'shipping_cost' => $order->get_total_shipping(),
				'total_tax' => $order->order_tax,
				'total_cost' => $order->order_total,
			],
			'adjustments' => [],
		];

		foreach ( $order->get_items() as $item ) {
			$order_meta['elements'][] = [
				'title' => $item['name'],
				'subtitle' => '',
				'quantity' => $item['qty'],
				'price' => $item['line_subtotal'],
				'currency' => $order->order_currency,
				'image_url' => $this->get_product_image_url( $item['product_id'] ),
			];
		}

		foreach ( $order->get_items( 'coupon' ) as $item ) {
			$order_meta['adjustments'][] = [
				'name' => $item['name'],
				'amount' => $item['discount_amount'],
			];
		}

		foreach ( $order->get_items( 'fee' ) as $item ) {
			$order_meta['adjustments'][] = [
				'name' => $item['name'],
				'amount' => $item['line_total'],
			];
		}

		return $order_meta;
	}

	private function get_product_image_url( $product_id ) {
		$product = new WC_Product( $product_id );
		$attachment_id = $product->get_gallery_attachment_ids()[0];
		return wp_get_attachment_image_src( $attachment_id )['url'];
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

	private function option( $option_suffix ) {
		return 'botamp_' . $option_suffix;
	}


	private function get_option( $name ) {
		$defaults = [
		'api_key' => '',
		'post_type' => 'post',
		'entity_description' => 'post_content',
		'entity_image_url' => 'post_thumbnail_url',
		'entity_title' => 'post_title',
		'entity_url' => 'post_permalink',
		];

		$option = get_option( $this->option( $name ) );

		if ( ! isset( $defaults[ $name ] ) || ( false !== $option && ! empty( $option ) ) ) {
			return $option;
		}

		return $defaults[ $name ];

	}
}
