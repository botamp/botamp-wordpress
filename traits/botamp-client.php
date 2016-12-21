<?php

trait Botamp_Client {
    private function get_botamp() {
        $botamp = new Botamp\Client( $this->get_option( 'api_key' ) );
		if ( defined( 'BOTAMP_API_BASE' ) ) {
			$botamp->setApiBase( BOTAMP_API_BASE );
		}

        return $botamp;
    }
}

?>
