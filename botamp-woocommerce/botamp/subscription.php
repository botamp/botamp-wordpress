<?php
require_once 'botamp-api-resource.php';

class Subscription extends Botamp_Api_Resource {

	public static function create( $entity, $contact ) {
		$subscription_attributes = [
			'entity_id' => $entity->getBody()['data']['id'],
			'subscription_type' => $entity->getBody()['data']['attributes']['entity_type'],
			'contact_id' => $contact->getBody()['data']['id'],
		];

		$subscription = parent::botamp()->subscriptions->create( $subscription_attributes );

		return $subscription;
	}

	public static function delete( $order_id ) {
		$subscription_id = get_post_meta( $order_id, 'botamp_subscription_id', true );

		parent::botamp()->subscriptions->delete( $subscription_id );
	}
}
