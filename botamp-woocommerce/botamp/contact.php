<?php
require_once 'botamp-api-resource.php';

class Contact extends Botamp_Api_Resource {

	public static function get( $contact_ref ) {
		try {
			$contact = parent::botamp()->contacts->get( $contact_ref );
			return $contact;
		} catch (Botamp\Exceptions\NotFound $ex) {
			return false;
		}
	}
}
