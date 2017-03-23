<?php

require_once 'class-contact.php';
require_once 'class-entitytype.php';
require_once 'class-me.php';
require_once 'class-optin.php';
require_once 'class-orderentity.php';
require_once 'class-productentity.php';
require_once 'class-subscription.php';

class ResourceProxy {

	private $resources = [];
	private $current_resource;

	public function __construct( $resource_code ) {
		add_action( 'shutdown', array( $this, 'gracefully_fail' ) );

		$this->resources = [
			'contact' => new Contact(),
			'entity_type' => new EntityType(),
			'me' => new Me(),
			'optin' => new Optin(),
			'order_entity' => new OrderEntity(),
			'product_entity' => new ProductEntity(),
			'subscription' => new Subscription(),
		];

		$this->current_resource = $this->resources[ $resource_code ];
	}

	public function __call( $method, $arguments ) {
		set_transient( 'botamp_auth_status', 'ok', HOUR_IN_SECONDS );

		try {
			if ( $this->current_resource == $this->resources['order_entity'] ) {
				if ( get_option( 'botamp_order_entity_type_created' ) !== 'ok' ) {
					(new EntityType())->create_or_update();
				}
			}
			return call_user_func_array( [ $this->current_resource, $method ], $arguments );
		} catch ( Botamp\Exceptions\Unauthorized $e ) {
			set_transient( 'botamp_auth_status', 'unauthorized', HOUR_IN_SECONDS );
			return false;
		}
	}

	public function gracefully_fail() {
		$last_error = error_get_last();
		if ( E_ERROR === $last_error['type'] ) {
			deactivate_plugins( plugin_dir_path( dirname( __FILE__ ) ) . 'botamp.php' );

			require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shutdown-alert.php';
			echo $shutdown_alert;

			exit;
		}
	}
}
