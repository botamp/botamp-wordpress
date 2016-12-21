<?php

class Subscription {

	public static function create( $botamp, $entity, $contact ) {
		$subscription_attributes = [
			'entity_id' => $entity->getBody()['data']['id'],
			'subscription_type' => $entity->getBody()['data']['attributes']['entity_type'],
			'contact_id' => $contact->getBody()['data']['id'],
		];

		$subscription = $botamp->subscriptions->create( $subscription_attributes );

		return $subscription;
	}

	public static function delete( $botamp, $order_id ) {
		$subscription_id = get_post_meta( $order_id, 'botamp_subscription_id', true );

		$botamp->subscriptions->delete( $subscription_id );
	}


}

