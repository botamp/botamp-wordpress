<?php

require_once 'abstract-resource.php';

class Me extends AbstractResource {

	public function get() {
		return $this->botamp->me->get();
	}
}
