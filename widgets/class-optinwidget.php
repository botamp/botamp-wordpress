<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/proxy-helper.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/option-helper.php';

class OptinWidget extends Wp_Widget {
	use OptionHelper;
	use ProxyHelper;

	public function __construct() {
		parent::__construct( 'optin_widget', __( 'Botamp Optin' , 'botamp' ) );
	}

	public function form( $instance ) {
		$optins = $this->get_proxy( 'optin' )->all()->getBody()['data'];

		$html = '<p><label>' . __( 'Optin to display:', 'botamp' )
				. "<select class='widefat' id='{$this->get_field_id('botamp_optin')}' name='{$this->get_field_name('botamp_optin')}'>";

		foreach ( $optins as $optin ) {
			$selected_attribute = ( isset( $instance['botamp_optin'] ) && $instance['botamp_optin'] == $optin['attributes']['name'] ) ? 'selected' : '';
			$html .= "<option value='{$optin['attributes']['name']}' {$selected_attribute}>{$optin['attributes']['name']}</option>";
		}

		echo $html .= '</select></label></p>';
	}

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $instance['botamp_optin'] ) ) {
			return;
		}

		echo $args['before_widget'];

		echo $this->get_proxy( 'optin' )
				  ->get( $instance['botamp_optin'] )
				  ->getBody()['data']['attributes']['embed_code'];

		echo $args['after_widget'];
	}


}
