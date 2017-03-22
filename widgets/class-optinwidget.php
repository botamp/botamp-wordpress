<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/proxy-helper.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/option-helper.php';

class OptinWidget extends Wp_Widget {
	use OptionHelper;
	use ProxyHelper;

	public function __construct() {
		parent::__construct( 'optin_widget', __( 'Botamp Optin Widget' , 'botamp' ) );
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];

	    echo $this->get_proxy( 'optin' )
				  ->get( $this->get_option( 'optin' ) )
				  ->getBody()['data']['attributes']['embed_code'];

		echo $args['after_widget'];
	}
}
