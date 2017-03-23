<?php

require_once 'class-abstractresource.php';

class Optin extends AbstractResource {

	public function all() {
		return $this->botamp->optins->all();
	}

	public function get( $name ) {
		return $this->botamp->optins->get( $name );
	}
}
