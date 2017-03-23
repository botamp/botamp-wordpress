<?php

require_once 'class-abstractresource.php';

class EntityType extends AbstractResource {

	public function all() {
		return $this->botamp->entityTypes->all();
	}

	public function create_or_update() {
		try {
			$entity_type = $this->botamp->entityTypes->get( 'order' );
			$this->update( $entity_type );
		} catch ( \Botamp\Exceptions\NotFound $e ) {
			$this->create();
		}

		update_option( 'botamp_order_entity_type_created', 'ok' );
	}

	private function create() {
		$this->botamp->entityTypes->create([
			'name' => 'order',
			'singular_label' => 'Order',
			'plural_label' => 'Orders',
			'platform' => 'woocommerce',
		]);
	}

	private function update( $entity_type ) {
		$entity_type_attributes = $entity_type->getBody()['data']['attributes'];
		if ( 'woocommerce' !== $entity_type_attributes['platform'] ) {
			$entity_type_attributes['platform'] = 'woocommerce';
			$entity_type_id = $entity_type->getBody()['data']['id'];

			$this->botamp->entityTypes->update( $entity_type_id, $entity_type_attributes );
		}
	}
}
