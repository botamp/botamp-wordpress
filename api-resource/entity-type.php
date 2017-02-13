<?php

require_once 'abstract-resource.php';

class EntityType extends AbstractResource {

	public function all() {
		return $this->botamp->entityTypes->all();
	}
}
