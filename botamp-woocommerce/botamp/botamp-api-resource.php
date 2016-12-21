<?php

class Botamp_Api_Resource {

	protected static function botamp() {
		$botamp = new Botamp\Client( get_option( 'botamp_api_key' ) );
		if ( defined( 'BOTAMP_API_BASE' ) ) {
			$botamp->setApiBase( BOTAMP_API_BASE );
		}

		return $botamp;
	}
}

