<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/option-helper.php';

class Botamp_Woocommerce_Admin {

	use OptionHelper;

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function register_settings() {
		add_settings_field(
			$this->option( 'order_notifications' ),
			__( 'Order notifications', 'botamp' ),
			array( $this, 'order_notifications_cb' ),
			$this->option( 'general_section' ),
			$this->option( 'general' ),
			array(
				'label_for' => $this->option( 'order_notifications' ),
			)
		);

		register_setting( $this->option( 'general_group' ), $this->option( 'order_notifications' ) );
	}


	public function order_notifications_cb() {
		$current_state = $this->get_option( 'order_notifications' );

		$html = '<input type="checkbox" name="' . $this->option( 'order_notifications' ) . '" value="enabled" ' .
		checked( 'enabled', $current_state, false ) . '/>';
			$html .= '<label for="' . $this->option( 'order_notifications' ) . '"> Send order notifications to customers </label>';

		echo $html;

	}
}
