<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

class Botamp_Public {

	private $plugin_name;

	private $version;

	private $botamp;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->botamp = new Botamp\Client( get_option( 'botamp_api_key' ) );
		if ( defined( 'BOTAMP_API_BASE' ) ) {
			$this->botamp->setApiBase( BOTAMP_API_BASE );
		}
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/botamp-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/botamp-public.js', array( 'jquery' ), $this->version, false );
	}

	public function create_or_update_entity( $post_id ) {
		if ( get_post_type( $post_id ) === get_option( 'botamp_post_type' )
				&& get_post_status( $post_id ) === 'publish' ) {

			$params = $this->get_fields_values( $post_id );

			foreach ( [ 'description', 'url', 'title' ] as $field ) {
				if ( ! isset( $params[ $field ] )
					|| empty( $params[ $field ] )
					|| false == $params[ $field ] ) {
						return false;
				}
			}

			if ( ! empty( $entity_id = get_post_meta( $post_id, 'botamp_entity_id', true ) ) ) {
				try {
					$response = $this->botamp->entities->get( $entity_id );
					$this->botamp->entities->update( $entity_id, $params );
					$this->set_auth_status( 'ok' );
				} catch (Botamp\Exceptions\NotFound $e) {
					$response = $this->botamp->entities->create( $params );
					update_post_meta( $post_id,
						'botamp_entity_id',
					$response->getBody()['data']['id']);
				} catch (Botamp\Exceptions\Unauthorized $e) {
					$this->set_auth_status( 'unauthorized' );
				}
			} else {
				try {
					$response = $this->botamp->entities->create( $params );
					add_post_meta( $post_id, 'botamp_entity_id', $response->getBody()['data']['id'] );
					$this->set_auth_status( 'ok' );
				} catch (Botamp\Exceptions\Unauthorized $e) {
					$this->set_auth_status( 'unauthorized' );
				}
			}
			return true;
		}
	}

	public function delete_entity( $post_id ) {
		if ( get_post_type( $post_id ) === get_option( 'botamp_post_type' )
		  	&& ! empty( $entity_id = get_post_meta( $post_id, 'botamp_entity_id', true ) ) ) {
			try {
				$this->botamp->entities->delete( $entity_id );
				$this->set_auth_status( 'ok' );
			} catch (Botamp\Exceptions\Unauthorized $e) {
				$this->set_auth_status( 'unauthorized' );
			}
		}
	}

	private function set_auth_status( $auth_status ) {
		if ( ! in_array( $auth_status, [ 'ok', 'unauthorized' ] ) ) {
			return;
		}
		if ( get_transient( 'botamp_auth_status' ) !== $auth_status  ) {
			set_transient( 'botamp_auth_status', $auth_status, HOUR_IN_SECONDS );
		}
	}

	private function set_operation_status( $operation_status ) {
		if ( ! in_array( $operation_status, [ 'ok', 'not_ok' ] ) ) {
			return;
		}
		if ( get_transient( 'botamp_operation_status' ) !== $operation_status ) {
			set_transient( 'botamp_operation_status', $operation_status, HOUR_IN_SECONDS );
		}
	}

	private function get_fields_values( $post_id ) {
		$post = get_post( $post_id, ARRAY_A );

		$values = [ 'entity_type' => 'article' ];

		foreach ( [ 'description', 'url', 'image_url', 'title' ] as $field ) {
			switch ( $option = get_option( 'botamp_entity_' . $field ) ) {
				case 'post_title':
					$values[ $field ] = apply_filters( 'the_title', $post['post_title'], $post_id );
					break;
				case 'post_excerpt':
					$values[ $field ] = apply_filters( 'get_the_excerpt', $post['post_excerpt'], $post_id );
					break;
				case 'post_content':
					$values[ $field ] = get_post_field( 'post_content', $post_id );
					break;
				case 'post_permalink';
					$values[ $field ] = get_the_permalink( $post_id );
					break;
				case 'post_thumbnail_url';
					$values[ $field ] = get_the_post_thumbnail_url( $post_id, 'full' );
					break;
				default:
					$values[ $field ] = get_post_meta( $post_id, $option, true );
					break;
			}
		}
		return $values;
	}
}
