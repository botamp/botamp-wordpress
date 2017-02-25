<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/option-helper.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/proxy-helper.php';

class Botamp_Woocommerce_Public {

	use OptionHelper;
	use ProxyHelper;

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function add_messenger_widget( $checkout ) {
		if ( $this->get_option( 'order_notifications' ) !== 'enabled' ) {
			return;
		}

		$page_attributes = $this->get_proxy( 'me' )->get()->getBody()['data']['attributes'];

		$ref = uniqid( "botamp_{$_SERVER['HTTP_HOST']}_", true );

		echo '<input type="hidden" name="botamp_contact_ref" value="' . $ref . '">
              <div id="notifications"><h3>' . __( 'Notifications' ) . '</h3>
			  <span>' . __( 'Get notifications about your order' ) . '</span>';

		require 'includes/messenger-script.php';

		printf( $messenger_script, $page_attributes['facebook_app_id'], $page_attributes['facebook_id'], $ref );
	}

	public function after_checkout( $order_id ) {
		$contact = $this->get_proxy( 'contact' )->get( $_POST['botamp_contact_ref'] );
		if ( false === $contact ) {
			return;
		}

		$entity = $this->get_proxy( 'order_entity' )->create( $order_id );

		$subscription = $this->get_proxy( 'subscription' )->create( $entity, $contact );

		add_post_meta( $order_id, 'botamp_subscription_id', $subscription->getBody()['data']['id'] );
	}

	public function after_order_status_changed( $order_id ) {
		$subscription_id = get_post_meta( $order_id, 'botamp_subscription_id', true );

		if ( empty( $subscription_id ) ) {
			return;
		}

		$this->get_proxy( 'order_entity' )->update( $order_id );
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
		if ( ! $has_orders || $this->botamp_orders() === false ) {
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

		$subscription_proxy = $this->get_proxy( 'subscription' );

		if ( 'all' === $param ) {

			foreach ( $this->botamp_orders() as $order_id ) {
				$subscription_proxy->delete( $order_id );
				delete_post_meta( $order_id, 'botamp_subscription_id' );
			}

			echo __( '<p>You have sucessfully unsubscribed from all your order notifications</p>' );
		} else {
			$subscription_proxy->delete( $param );
			delete_post_meta( $param, 'botamp_subscription_id' );

			echo __( '<p>You have sucessfully unsubscribed from your order notifications</p>' );
		}

	}

	private function unsubscribe_endpoint_url( $param ) {
		$myaccount_id = get_option( 'woocommerce_myaccount_page_id' );
		$myaccount_url = get_permalink( $myaccount_id );
		return "{$myaccount_url}botamp_order_unsubscribe/{$param}";
	}

	private function botamp_orders() {
		global $wpdb;
		$order_ids = $wpdb->get_col( "select distinct post_id from {$wpdb->prefix}postmeta
										where meta_key = 'botamp_subscription_id'", 0 );

		return empty( $order_ids ) ? false : $order_ids;
	}
}
