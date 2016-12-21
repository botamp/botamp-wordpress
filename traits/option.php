<?php

trait Option {
	private function option( $option_suffix ) {
		return 'botamp_' . $option_suffix;
	}

	private function get_option( $option_suffix ) {
		$defaults = [
		'api_key' => '',
		'post_type' => 'post',
		'entity_description' => 'post_content',
		'entity_image_url' => 'post_thumbnail_url',
		'entity_title' => 'post_title',
		'entity_url' => 'post_permalink',
		];

		$option = get_option( $this->option( $option_suffix ) );

		return (false !== $option) ? $option : $defaults[ $option_suffix ];
	}
}


