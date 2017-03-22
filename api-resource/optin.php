<?php

require_once 'abstract-resource.php';

class Optin extends AbstractResource {

    public function all() {
        return $this->botamp->optins->all();
    }

	public function get( $name ) {
		return $this->botamp->optins->get( $name );
	}
}
