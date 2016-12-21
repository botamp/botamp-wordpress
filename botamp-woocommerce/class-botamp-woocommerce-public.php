<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/option.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'traits/botamp-client.php';

class Botamp_Woocommerce_Public {

    use Option;
    use Botamp_Client;

    private $plugin_name;

    private $version;

    private $botamp;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->botamp = $this->get_botamp();
    }

    public function add_messenger_widget( $checkout ) {
		if ( $this->get_option( 'order_notifications' ) !== 'enabled' ) {
			return;
		}

		$page_attributes = $this->botamp->me->get()->getBody()['data']['attributes'];

		$ref = uniqid( "botamp_{$_SERVER['HTTP_HOST']}_", true );

		echo '<input type="hidden" name="botamp_contact_ref" value="' . $ref . '">
              <div id="notifications"><h3>' . __( 'Notifications' ) . '</h3>';

        require 'includes/messenger_script.php';

        printf( $messenger_script, $page_attributes['facebook_app_id'],
                                   $page_attributes['facebook_id'],
                                   $ref );
	}

    public function after_checkout( $order_id ) {
		$contact = $this->get_contact( $_POST['botamp_contact_ref'] );
		if ( false === $contact ) {
			return;
		}

		$entity = $this->create_entity( $order_id );

		$subscription = $this->create_subscription( $entity, $contact );

		add_post_meta( $order_id, 'botamp_subscription_id', $subscription->getBody()['data']['id'] );
	}

    public function update_entity( $order_id ) {
		$subscription_id = get_post_meta( $order_id, 'botamp_subscription_id', true );

		if ( empty( $subscription_id ) ) {
			return;
		}

		$order = new WC_Order( $order_id );

		$subscription = $this->botamp->subscriptions->get( $subscription_id );
		$entity_id = $subscription->getBody()['data']['attributes']['entity_id'];
		$entity = $this->botamp->entities->get( $entity_id );

		$entity_attributes = $entity->getBody()['data']['attributes'];
		$entity_attributes['status'] = $order->get_status();
		$entity_attributes['meta'] = $this->get_order_meta( $order );

		$this->botamp->entities->update( $entity_id, $entity_attributes );
	}

    public function add_unsubscribe_button( $actions, $order ) {
		$subscription_id = get_post_meta( $order->id, 'botamp_subscription_id', true );

		if ( empty( $subscription_id ) ) {
			return;
		}

		$actions['botamp_unsubscribe_button'] = [
			'name' => __( 'Unsubscribe from notifications', 'botamp' ),
			'url' => $this->unsubscribe_endpoint_url( $order->id ),
		];

		return $actions;
	}

    public function add_unsubscribe_all_button( $has_orders ) {
		if ( ! $has_orders || $this->orders_subscribed() === false ) {
			return;
		}

		$url = $this->unsubscribe_endpoint_url( 'all' );

		echo "<div id='botamp_unsubscribe_container'><a href= '{$url}' class='button botamp_unsubscribe_button'>" .
			__( 'Unsubscribe from all your order notifications', 'botamp' ) .
			'</a></div>';
	}

    public function add_query_vars( $vars ) {
		$vars[] = 'botamp_order_unsubscribe';
        return $vars;
	}

    public function unsubscribe() {
		global $wp;
		$current_url = home_url( add_query_arg( array(),$wp->request ) );

		$param = substr( $current_url, strrpos( $current_url,'/' ) + 1 );

		if ( 'all' === $param ) {

			foreach ( $this->orders_subscribed() as $order_id ) {
				$this->delete_subscription( $order_id );
			}

			echo __( '<p>You have sucessfully unsubscribed from all your order notifications</p>' );
		} else {
			$this->delete_subscription( $param );

			echo __( '<p>You have sucessfully unsubscribed from your order notifications</p>' );
		}

	}

    private function create_entity( $order_id ) {
		$order = new WC_Order( $order_id );
		$order_meta = $this->get_order_meta( $order );

		$entity_attributes = [
			'title' => $order_meta['order_number'] . ' - ' . $order_meta['recipient_name'],
			'url' => $order_meta['order_url'],
			'entity_type' => 'order',
			'status' => $order->get_status(),
			'meta' => $order_meta,
		];

		$entity = $this->botamp->entities->create( $entity_attributes );

		return $entity;
	}

	private function create_subscription( $entity, $contact ) {
		$subscription_attributes = [
			'entity_id' => $entity->getBody()['data']['id'],
			'subscription_type' => $entity->getBody()['data']['attributes']['entity_type'],
			'contact_id' => $contact->getBody()['data']['id'],
		];

		$subscription = $this->botamp->subscriptions->create( $subscription_attributes );

		return $subscription;
	}

	private function delete_subscription( $order_id ) {
		$subscription_id = get_post_meta( $order_id, 'botamp_subscription_id', true );

		$this->botamp->subscriptions->delete( $subscription_id );

		delete_post_meta( $order_id, 'botamp_subscription_id' );
	}

	private function get_contact( $contact_ref ) {
		try {
			$contact = $this->botamp->contacts->get( $contact_ref );
			return $contact;
		} catch (Botamp\Exceptions\NotFound $ex) {
			return false;
		}
	}

	private function get_order_meta( $order_id ) {
		$order = new WC_Order( $order_id );

		$order_meta = [
			'recipient_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'order_number' => $order->get_order_number(),
			'currency' => $order->order_currency,
			'payment_method' => $order->payment_method_title,
			'order_url' => $order->get_view_order_url(),
			'timestamp' => strtotime( $order->order_date ),
			'address' => [
				'street_1' => $order->billing_address_1,
				'street_2' => $order->billing_address_2,
				'city' => $order->billing_city,
				'postal_code' => $order->billing_postcode,
				'state' => $order->billing_state,
				'country' => $order->billing_country,
			],
			'elements' => [],
			'summary' => [
				'subtotal' => $order->get_subtotal(),
				'shipping_cost' => $order->get_total_shipping(),
				'total_tax' => $order->order_tax,
				'total_cost' => $order->order_total,
			],
			'adjustments' => [],
		];

		foreach ( $order->get_items() as $item ) {
			$order_meta['elements'][] = [
				'title' => $item['name'],
				'subtitle' => '',
				'quantity' => $item['qty'],
				'price' => $item['line_subtotal'],
				'currency' => $order->order_currency,
				'image_url' => $this->get_product_image_url( $item['product_id'] ),
			];
		}

		foreach ( $order->get_items( 'coupon' ) as $item ) {
			$order_meta['adjustments'][] = [
				'name' => $item['name'],
				'amount' => $item['discount_amount'],
			];
		}

		foreach ( $order->get_items( 'fee' ) as $item ) {
			$order_meta['adjustments'][] = [
				'name' => $item['name'],
				'amount' => $item['line_total'],
			];
		}

		return $order_meta;
	}

	private function get_product_image_url( $product_id ) {
		$product = new WC_Product( $product_id );
		$attachment_id = $product->get_gallery_attachment_ids()[0];
		return wp_get_attachment_image_src( $attachment_id )['url'];
	}

    private function unsubscribe_endpoint_url( $param = '' ) {
		$myaccount_id = get_option( 'woocommerce_myaccount_page_id' );
		$myaccount_url = get_permalink( $myaccount_id );
		return "{$myaccount_url}botamp_order_unsubscribe/{$param}";
	}

	private function orders_subscribed() {
		global $wpdb;
		$order_ids = $wpdb->get_col( "select distinct post_id from {$wpdb->prefix}postmeta
										where meta_key = 'botamp_subscription_id'", 0 );

		return empty( $order_ids ) ? false : $order_ids;
	}
}
?>
