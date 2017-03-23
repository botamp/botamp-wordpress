<?php
require_once 'class-abstractresource.php';

class OrderEntity extends AbstractResource {

	public function create( $order_id ) {
		$order = new WC_Order( $order_id );
		$order_meta = $this->get_order_meta( $order );

		$entity_attributes = [
			'title' => $order_meta['order_number'] . ' - ' . $order_meta['recipient_name'],
			'url' => $order_meta['order_url'],
			'entity_type' => 'order',
			'status' => $order->get_status(),
			'meta' => $order_meta,
		];

		return $this->botamp->entities->create( $entity_attributes );
	}

	public function update( $order_id ) {
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

		return $this->botamp->entities->update( $entity_id, $entity_attributes );
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
		$image_id = get_post_thumbnail_id( $product_id );
		return wp_get_attachment_image_src( $image_id, 'single-post-thumbnail' );
	}
}
