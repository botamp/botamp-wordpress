<?php

require_once 'class-abstractresource.php';

class ProductEntity extends AbstractResource {

	public function create( $params ) {
		return $this->botamp->entities->create( $params );
	}

	public function update( $entity_id, $params ) {
		return $this->botamp->entities->update( $entity_id, $params );
	}

	public function delete( $entity_id ) {
		return $this->botamp->entities->delete( $entity_id );
	}
}

