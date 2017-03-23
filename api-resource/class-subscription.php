<?php
require_once 'class-abstractresource.php';

class Subscription extends AbstractResource {

	public function create( $entity, $contact ) {
		$subscription_attributes = [
			'entity_id' => $entity->getBody()['data']['id'],
			'subscription_type' => $entity->getBody()['data']['attributes']['entity_type'],
			'contact_id' => $contact->getBody()['data']['id'],
		];

		return $this->botamp->subscriptions->create( $subscription_attributes );
	}

	public function delete( $order_id ) {
		$subscription_id = get_post_meta( $order_id, 'botamp_subscription_id', true );

		return $this->botamp->subscriptions->delete( $subscription_id );
	}
}
