<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api-resource/class-resourceproxy.php';

trait ProxyHelper {
	private function get_proxy( $resource_code ) {
		return new ResourceProxy( $resource_code );
	}
}
