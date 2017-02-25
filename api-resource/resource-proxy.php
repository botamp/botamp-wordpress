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
		add_action( 'shutdown', array( $this, 'gracefully_fail' ) );

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
            if($this->current_resource == $this->resources['order_entity']) {
                if( get_option('botamp_order_entity_type_created') !== 'ok' )
                    (new EntityType())->create_or_update();
            }
			return call_user_func_array( [ $this->current_resource, $method ], $arguments );
		} catch (Botamp\Exceptions\Unauthorized $e) {
			set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
			return false;
		}
	}

	public function gracefully_fail() {
        $last_error = error_get_last();
        if ($last_error['type'] === E_ERROR) {
            deactivate_plugins( plugin_dir_path( dirname( __FILE__ ) ) . 'botamp.php' );

    		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shutdown-alert.php';
    		echo $shutdown_alert;

    		exit;
        }
	}
}
