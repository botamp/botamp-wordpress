<?php
require_once 'abstract-resource.php';

class Contact extends AbstractResource {

	public function get( $contact_ref ) {
		try {
			return $this->botamp->contacts->get( $contact_ref );
		} catch (Botamp\Exceptions\NotFound $ex) {
			return false;
		}
	}
}
