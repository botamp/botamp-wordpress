<?php

class AbstractResource {

	protected $botamp;

	public function __construct() {
		$this->set_botamp_client();
	}

	public function set_botamp_client( $api_key = '' ) {
		$this->botamp = new Botamp\Client( '' == $api_key ? get_option( 'botamp_api_key' ) : $api_key );
		if ( defined( 'BOTAMP_API_BASE' ) ) {
			$this->botamp->setApiBase( BOTAMP_API_BASE );
		}
	}
}
