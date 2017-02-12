<?php

require_once 'product-entity.php';
require_once 'order-entity.php';
require_once 'contact.php';
require_once 'subscription.php';
require_once 'me.php';
require_once 'entity-type.php';

class ResourceProxy {

	private $resources = [];
	private $current_resource;

	public function __construct( $resource_code ) {
		$this->resources = [
			'product_entity' => new ProductEntity(),
			'order_entity' => new OrderEntity(),
			'contact' => new Contact(),
			'subscription' => new Subscription(),
			'me' => new Me(),
            'entity_type' => new EntityType(),
		];

		$this->current_resource = $this->resources[ $resource_code ];
	}

	public function __call( $method, $arguments ) {
		set_transient( 'botamp_auth_status', 'ok', HOUR_IN_SECONDS );

		try {
			return call_user_func_array( [ $this->current_resource, $method ], $arguments );
		} catch (Botamp\Exceptions\Unauthorized $e) {
			set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
			return false;
		}
	}
}
