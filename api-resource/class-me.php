<?php

require_once 'class-abstractresource.php';

class Me extends AbstractResource {

	public function get() {
		return $this->botamp->me->get();
	}
}
