<?php

class Contact {

	public static function get( $botamp, $contact_ref ) {
		try {
			$contact = $botamp->contacts->get( $contact_ref );
			return $contact;
		} catch (Botamp\Exceptions\NotFound $ex) {
			return false;
		}
	}
}

