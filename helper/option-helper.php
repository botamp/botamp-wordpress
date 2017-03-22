<?php

trait OptionHelper {
	private function option( $option_suffix ) {
		return 'botamp_' . $option_suffix;
	}

	private function get_option( $option_suffix ) {
		$defaults = [
		'entity_description' => 'post_content',
		'entity_image_url' => 'post_thumbnail_url',
		'entity_title' => 'post_title',
		'entity_url' => 'post_permalink',
		'post_type' => 'post',
		];

		$option = get_option( $this->option( $option_suffix ) );

		if ( false === $option ) {
			return in_array( $option_suffix, $defaults ) ? $defaults[ $option_suffix ] : false;
		} else {
			return $option;
		}

	}
}
